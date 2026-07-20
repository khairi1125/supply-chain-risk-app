<div class="card mb-4" style="border-radius: 10px;">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-chart-simple"></i> Quick Statistics</h5>
    </div>
    <div class="card-body">
        {{-- Average Risk Score --}}
        <div class="mb-3">
            <small class="text-muted d-block mb-1">Average Risk Score</small>
            <h4 class="mb-0">{{ number_format($stats['average_score'], 2) }}</h4>
        </div>

        <hr>

        {{-- Highest Risk Country --}}
        <div class="mb-3">
            <small class="text-muted d-block mb-1">Highest Risk Country</small>
            @if($stats['highest_risk']['country'])
                <strong>{{ $stats['highest_risk']['country'] }}</strong>
                <span class="badge bg-danger ms-2">{{ number_format($stats['highest_risk']['score'], 1) }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </div>

        <hr>

        {{-- Lowest Risk Country --}}
        <div class="mb-3">
            <small class="text-muted d-block mb-1">Lowest Risk Country</small>
            @if($stats['lowest_risk']['country'])
                <strong>{{ $stats['lowest_risk']['country'] }}</strong>
                <span class="badge bg-success ms-2">{{ number_format($stats['lowest_risk']['score'], 1) }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </div>

        <hr>

        {{-- Most Recently Added --}}
        <div>
            <small class="text-muted d-block mb-1">Most Recently Added</small>
            @if($stats['most_recent'])
                <strong>{{ $stats['most_recent']['country'] }}</strong>
                <br>
                <small class="text-muted">{{ $stats['most_recent']['created_at']->diffForHumans() }}</small>
            @else
                <span class="text-muted">-</span>
            @endif
        </div>
    </div>
</div>
