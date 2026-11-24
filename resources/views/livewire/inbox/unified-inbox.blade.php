<div class="unified-inbox-wrapper" style="height: calc(100vh - 120px);">
    <div class="card border-0 shadow-sm h-100 overflow-hidden">
        <div class="row g-0 h-100">
            <!-- Column 1: Folders & Filters Sidebar -->
            <div class="col-lg-3 col-md-4 border-end h-100 d-flex flex-column bg-light">
                <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-chat-dots-fill text-primary me-2"></i> Social Inbox
                        </h5>
                        <small class="text-muted">Unified comments & direct messages</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-xs btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightning-charge"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <button class="dropdown-item small d-flex align-items-center gap-2" wire:click="syncLiveComments">
                                    <i class="bi bi-arrow-repeat text-primary"></i> Sync Live Comments
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item small d-flex align-items-center gap-2" wire:click="generateTestInteraction">
                                    <i class="bi bi-plus-circle text-success"></i> Add Test Interaction
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Folders Navigation -->
                <div class="p-3 border-bottom">
                    <div class="nav flex-column nav-pills gap-1">
                        <button wire:click="setFolder('open')" class="nav-link text-start d-flex justify-content-between align-items-center py-2 px-3 {{ $folder === 'open' ? 'active' : 'text-dark' }}">
                            <span><i class="bi bi-inbox me-2"></i> All Open</span>
                            <span class="badge {{ $folder === 'open' ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} rounded-pill">{{ $metrics['open'] }}</span>
                        </button>

                        <button wire:click="setFolder('unread')" class="nav-link text-start d-flex justify-content-between align-items-center py-2 px-3 {{ $folder === 'unread' ? 'active' : 'text-dark' }}">
                            <span><i class="bi bi-envelope-open me-2"></i> Unread</span>
                            <span class="badge {{ $folder === 'unread' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger' }} rounded-pill">{{ $metrics['unread'] }}</span>
                        </button>

                        <button wire:click="setFolder('assigned_me')" class="nav-link text-start d-flex justify-content-between align-items-center py-2 px-3 {{ $folder === 'assigned_me' ? 'active' : 'text-dark' }}">
                            <span><i class="bi bi-person-check me-2"></i> Assigned to Me</span>
                            <span class="badge {{ $folder === 'assigned_me' ? 'bg-white text-indigo' : 'bg-secondary-subtle text-secondary' }} rounded-pill">{{ $metrics['assigned_me'] }}</span>
                        </button>

                        <button wire:click="setFolder('resolved')" class="nav-link text-start d-flex justify-content-between align-items-center py-2 px-3 {{ $folder === 'resolved' ? 'active' : 'text-dark' }}">
                            <span><i class="bi bi-check2-circle me-2"></i> Resolved</span>
                            <span class="badge {{ $folder === 'resolved' ? 'bg-white text-success' : 'bg-success-subtle text-success' }} rounded-pill">{{ $metrics['resolved'] }}</span>
                        </button>

                        <button wire:click="setFolder('spam')" class="nav-link text-start d-flex justify-content-between align-items-center py-2 px-3 {{ $folder === 'spam' ? 'active' : 'text-dark' }}">
                            <span><i class="bi bi-archive me-2"></i> Spam & Archived</span>
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-3 flex-grow-1 overflow-y-auto">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Filter by Client</label>
                        <select wire:model.live="selectedClientId" class="form-select form-select-sm">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Social Platform</label>
                        <select wire:model.live="selectedPlatform" class="form-select form-select-sm">
                            <option value="">All Social Networks</option>
                            @foreach($platforms as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Column 2: Thread List -->
            <div class="col-lg-4 col-md-4 border-end h-100 d-flex flex-column bg-white">
                <!-- Search header -->
                <div class="p-3 border-bottom">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control bg-light border-start-0" placeholder="Search sender or message...">
                    </div>
                </div>

                <!-- Conversation list -->
                <div class="flex-grow-1 overflow-y-auto">
                    @forelse($conversations as $conv)
                        @php
                            $isSelected = $selectedConversationId === $conv->id;
                            $plat = $conv->socialProfile?->platform;
                        @endphp
                        <div 
                            wire:click="selectConversation({{ $conv->id }})" 
                            class="p-3 border-bottom cursor-pointer transition-all {{ $isSelected ? 'bg-primary-subtle border-start border-start-4 border-start-primary' : 'hover-bg-light' }}"
                            style="cursor: pointer;"
                        >
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="position-relative">
                                        @if($conv->sender_avatar)
                                            <img src="{{ $conv->sender_avatar }}" alt="{{ $conv->sender_name }}" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;">
                                        @else
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($conv->sender_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <span class="position-absolute bottom-0 end-0 badge rounded-pill p-1 {{ $plat?->cssClass() ?? 'bg-primary' }}" style="font-size: 0.55rem;">
                                            <i class="bi {{ $plat?->icon() }}"></i>
                                        </span>
                                    </div>
                                    <span class="fw-bold text-dark small text-truncate" style="max-width: 140px;">
                                        {{ $conv->sender_name }}
                                    </span>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $conv->last_activity_at->diffForHumans(null, true) }}</small>
                            </div>

                            <p class="small text-muted text-truncate mb-1" style="font-size: 0.8rem;">
                                {{ $conv->snippet ?: 'No messages yet.' }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="badge bg-light text-muted border small" style="font-size: 0.65rem;">
                                    <i class="bi {{ $conv->type->icon() }} me-1"></i> {{ $conv->type->label() }}
                                </span>
                                @if($conv->is_unread)
                                    <span class="badge bg-danger rounded-circle p-1" title="Unread"></span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 px-3 text-muted small">
                            <i class="bi bi-chat-left-dots fs-1 text-secondary opacity-50 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark mb-1">No Conversations in this View</h6>
                            <p class="text-muted small mb-3">Sync live comments from your connected social pages or create a test interaction.</p>
                            <div class="d-flex flex-column gap-2">
                                <button wire:click="syncLiveComments" wire:loading.attr="disabled" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center gap-1">
                                    <span wire:loading.remove wire:target="syncLiveComments"><i class="bi bi-arrow-repeat"></i> Sync Live Comments</span>
                                    <span wire:loading wire:target="syncLiveComments"><span class="spinner-border spinner-border-sm"></span> Syncing...</span>
                                </button>
                                <button wire:click="generateTestInteraction" class="btn btn-sm btn-light border text-dark">
                                    <i class="bi bi-plus-circle text-success me-1"></i> Add Test Interaction
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 3: Active Conversation & Reply Console -->
            <div class="col-lg-5 col-md-4 h-100 d-flex flex-column bg-light">
                @if($activeConversation)
                    @php $activePlat = $activeConversation->socialProfile?->platform; @endphp
                    <!-- Thread Header -->
                    <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $activeConversation->sender_name }}</h6>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-light border text-dark p-1 d-inline-flex align-items-center gap-1" style="font-size: 0.7rem;">
                                        <i class="bi {{ $activePlat?->icon() }} {{ $activePlat?->cssClass() }}"></i>
                                        {{ $activeConversation->socialProfile?->name }}
                                    </span>
                                    @if($activeConversation->socialProfile?->client)
                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                            {{ $activeConversation->socialProfile->client->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex align-items-center gap-2">
                            @if($activeConversation->status === \App\Enums\ConversationStatus::RESOLVED)
                                <button wire:click="reopen" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reopen
                                </button>
                            @else
                                <button wire:click="markResolved" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check2-circle me-1"></i> Resolve
                                </button>
                            @endif

                            <!-- Assign Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-plus"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><h6 class="dropdown-header">Assign Staff Member</h6></li>
                                    @foreach($teamMembers as $member)
                                        <li>
                                            <a class="dropdown-item small" href="#" wire:click.prevent="$set('assignToUserId', {{ $member->id }}); assignToMember();">
                                                {{ $member->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Flash notice -->
                    @if($feedbackMessage)
                        <div class="alert alert-{{ $feedbackType }} alert-dismissible fade show m-2 py-1 px-3 small" role="alert">
                            {{ $feedbackMessage }}
                            <button type="button" class="btn-close py-1" wire:click="$set('feedbackMessage', null)"></button>
                        </div>
                    @endif

                    <!-- Message History Bubbles -->
                    <div class="flex-grow-1 p-3 overflow-y-auto d-flex flex-column gap-3">
                        @foreach($activeConversation->messages as $msg)
                            @if($msg->direction->isInbound())
                                <!-- Inbound Bubble (Left) -->
                                <div class="d-flex align-items-start gap-2" style="max-width: 85%;">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="bg-white p-3 rounded-3 shadow-xs border">
                                            <div class="fw-semibold text-dark small mb-1">{{ $msg->sender_name }}</div>
                                            <div class="text-dark small" style="white-space: pre-wrap;">{{ $msg->body }}</div>
                                        </div>
                                        <small class="text-muted ms-1" style="font-size: 0.65rem;">{{ $msg->sent_at->format('M d, h:i A') }}</small>
                                    </div>
                                </div>
                            @else
                                <!-- Outbound Bubble (Right) -->
                                <div class="d-flex align-items-start gap-2 align-self-end flex-row-reverse" style="max-width: 85%;">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="text-end">
                                        <div class="bg-primary text-white p-3 rounded-3 shadow-xs text-start">
                                            <div class="small fw-semibold mb-1 text-white-50">{{ $msg->sender_name }} (Agency)</div>
                                            <div class="small" style="white-space: pre-wrap;">{{ $msg->body }}</div>
                                        </div>
                                        <small class="text-muted me-1" style="font-size: 0.65rem;">{{ $msg->sent_at->format('M d, h:i A') }}</small>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Quick Canned Responses -->
                    <div class="p-2 px-3 bg-white border-top border-bottom d-flex gap-1 overflow-x-auto">
                        <span class="text-muted small me-1 align-self-center" style="font-size: 0.75rem;">Quick:</span>
                        <button type="button" wire:click="applyTemplate('Thanks for reaching out! We appreciate your support.')" class="btn btn-xs btn-outline-secondary rounded-pill text-nowrap">
                            Thanks for reaching out
                        </button>
                        <button type="button" wire:click="applyTemplate('Hello! We have sent you a direct message with more details.')" class="btn btn-xs btn-outline-secondary rounded-pill text-nowrap">
                            Sent DM details
                        </button>
                        <button type="button" wire:click="applyTemplate('Please visit our website link in bio to place your order!')" class="btn btn-xs btn-outline-secondary rounded-pill text-nowrap">
                            Link in bio
                        </button>
                    </div>

                    <!-- Reply Console -->
                    <div class="p-3 bg-white">
                        <div class="input-group">
                            <textarea wire:model="replyText" wire:keydown.cmd.enter="sendReply" wire:keydown.ctrl.enter="sendReply" class="form-control" rows="2" placeholder="Write a public reply as {{ $activeConversation->socialProfile?->name }}..."></textarea>
                            <button wire:click="sendReply" wire:loading.attr="disabled" class="btn btn-primary px-3 d-flex align-items-center">
                                <span wire:loading.remove wire:target="sendReply"><i class="bi bi-send-fill"></i></span>
                                <span wire:loading wire:target="sendReply"><span class="spinner-border spinner-border-sm"></span></span>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" style="font-size: 0.7rem;">Press Ctrl+Enter to send immediately.</small>
                            <small class="text-muted" style="font-size: 0.7rem;">Official Meta API Integration</small>
                        </div>
                    </div>

                @else
                    <!-- Empty State -->
                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-5 text-muted">
                        <i class="bi bi-chat-square-text fs-1 text-secondary opacity-50 mb-3"></i>
                        <h6 class="fw-bold text-dark mb-1">No Conversation Selected</h6>
                        <p class="small text-muted mb-4" style="max-width: 320px;">
                            Select a thread from the list on the left to read messages, send replies, or assign tickets.
                        </p>
                        <button wire:click="generateTestInteraction" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Test Conversation
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

