<?php

namespace App\Services\AI;

use App\Models\UsageRecord;
use App\Models\Workspace;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIAssistantService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $endpoint;

    public function __construct()
    {
        $groqKey = config('services.groq.api_key') ?: env('GROQ_API_KEY');
        $openaiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');

        if (!empty($groqKey) || str_starts_with((string) $openaiKey, 'gsk_')) {
            $this->apiKey = $groqKey ?: $openaiKey;
            $this->model = config('services.groq.model', 'llama-3.3-70b-versatile');
            $this->endpoint = 'https://api.groq.com/openai/v1/chat/completions';
        } else {
            $this->apiKey = $openaiKey;
            $this->model = config('services.openai.model', 'gpt-4o-mini');
            $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        }
    }

    /**
     * Generate engaging platform-optimized social media captions.
     */
    public function generateCaption(
        Workspace $workspace,
        string $topic,
        string $tone = 'engaging',
        string $platform = 'general',
        ?string $cta = null
    ): string {
        $prompt = "Write an engaging social media post caption about: '{$topic}'.\n";
        $prompt .= "Target Platform: {$platform}.\n";
        $prompt .= "Tone of voice: {$tone}.\n";
        if ($cta) {
            $prompt .= "Call to Action to include: {$cta}.\n";
        }
        $prompt .= "Include appropriate emojis and 3-5 relevant hashtags at the bottom.";

        $response = $this->callAiOrFallback($prompt, function () use ($topic, $tone, $cta) {
            $emojis = match ($tone) {
                'humorous' => '😂🔥✨',
                'urgent' => '⚡🚨⏳',
                'educational' => '💡📚🎯',
                'professional' => '📊🤝💼',
                default => '🚀✨🎉',
            };
            $ctaLine = $cta ? "\n\n👉 {$cta}" : "\n\nDrop a comment below and let us know your thoughts! 👇";
            return "{$emojis} {$topic}!\n\nWhether you're gearing up for the week or looking to take things to the next level, staying ahead makes all the difference.{$ctaLine}\n\n#socialmedia #growth #innovation";
        });

        $this->trackUsage($workspace, 'ai_requests_count', 1);
        $this->trackUsage($workspace, 'ai_tokens_used', Str::wordCount($response) * 2);

        return $response;
    }

    /**
     * Rewrite, polish, shorten, or punch up existing social media content.
     */
    public function rewriteContent(Workspace $workspace, string $content, string $mode = 'shorter'): string
    {
        $prompt = match ($mode) {
            'shorter' => "Make the following social media post shorter, punchier, and remove any fluff while preserving key message:\n\n{$content}",
            'punchier' => "Make the following social media post much more exciting, high-converting, and dynamic with strong hooks:\n\n{$content}",
            'bullet_points' => "Format the following social media post into clear, readable bullet points with emojis:\n\n{$content}",
            'fix_grammar' => "Proofread and polish the grammar, punctuation, and flow of the following post without changing its meaning:\n\n{$content}",
            default => "Rewrite and optimize the following social media caption:\n\n{$content}",
        };

        $response = $this->callAiOrFallback($prompt, function () use ($content, $mode) {
            if ($mode === 'shorter') {
                return Str::limit($content, 80) . " 🚀";
            }
            if ($mode === 'bullet_points') {
                return "Key Takeaways:\n• Fast results\n• High engagement\n• Built for growth\n\n" . $content;
            }
            return "✨ " . trim($content) . " ✨";
        });

        $this->trackUsage($workspace, 'ai_requests_count', 1);
        $this->trackUsage($workspace, 'ai_tokens_used', Str::wordCount($response) * 2);

        return $response;
    }

    /**
     * Generate high-engagement trending hashtags for a post.
     */
    public function generateHashtags(
        Workspace $workspace,
        string $content,
        int $count = 8,
        string $platform = 'instagram'
    ): array {
        $prompt = "Generate {$count} high-performing, targeted hashtags for this post on {$platform}:\n\n{$content}\n\nReturn only space-separated hashtags.";

        $result = $this->callAiOrFallback($prompt, function () use ($content, $count) {
            $words = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', strtolower($content))));
            $hashtags = [];
            foreach (array_slice($words, 0, $count) as $w) {
                if (strlen($w) > 3) {
                    $hashtags[] = '#' . $w;
                }
            }
            if (empty($hashtags)) {
                $hashtags = ['#trending', '#social', '#business', '#marketing'];
            }
            return implode(' ', $hashtags);
        });

        $this->trackUsage($workspace, 'ai_requests_count', 1);

        preg_match_all('/#\w+/', $result, $matches);
        return !empty($matches[0]) ? $matches[0] : ['#marketing', '#growth', '#social'];
    }

    /**
     * Track monthly consumable resource counts per workspace.
     */
    public function trackUsage(Workspace $workspace, string $metricKey, int $amount = 1): void
    {
        $cycleMonth = now()->format('Y-m');

        $record = UsageRecord::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'metric_key' => $metricKey,
                'billing_cycle_month' => $cycleMonth,
            ],
            [
                'usage_count' => 0,
            ]
        );

        $record->increment('usage_count', $amount);
    }

    /**
     * Dispatch HTTP request to OpenAI or execute deterministic fallback.
     */
    protected function callAiOrFallback(string $prompt, callable $fallback): string
    {
        if (empty($this->apiKey) || app()->environment('testing')) {
            return $fallback();
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(20)
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert social media copywriter and growth marketer.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.message.content') ?? '');
            }

            Log::warning("OpenAI API call returned error: " . $response->body());
            return $fallback();

        } catch (Exception $e) {
            Log::error("OpenAI API exception: " . $e->getMessage());
            return $fallback();
        }
    }
}

