<div class="card" style="border-radius: 10px;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-clock-rotate-left"></i> Recent Activity</h5>
    </div>
    <div class="card-body">
        @if(count($activity) > 0)
            <div class="list-group list-group-flush">
                @foreach($activity as $log)
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            @if($log->action === 'watchlist_added')
                                <i class="fas fa-plus-circle text-success"></i>
                            @elseif($log->action === 'watchlist_removed')
                                <i class="fas fa-minus-circle text-danger"></i>
                            @else
                                <i class="fas fa-rotate text-primary"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <small class="d-block">{{ $log->description }}</small>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-muted text-center mb-0">No recent activity</p>
        @endif
    </div>
</div>
