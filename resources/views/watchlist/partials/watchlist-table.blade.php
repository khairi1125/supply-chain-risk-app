<div class="card" style="border-radius: 10px;">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="watchlistTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="sortable" data-sort="country">
                            Country <i class="fas fa-sort"></i>
                        </th>
                        <th>Flag</th>
                        <th>Region</th>
                        <th>Currency</th>
                        <th class="sortable" data-sort="risk">
                            Risk Score <i class="fas fa-sort"></i>
                        </th>
                        <th>Risk Level</th>
                        <th>Temperature</th>
                        <th>Exchange Rate</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($watchlists as $index => $item)
                    <tr data-country="{{ strtolower($item['country_name']) }}" 
                        data-region="{{ $item['country']->region ?? '' }}" 
                        data-risk-level="{{ $riskLevel ?? ($item['risk_score']['risk_level'] ?? 'low') }}"
                        data-risk-score="{{ $item['risk_score']['total_score'] ?? 0 }}">
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item['country_name'] }}</strong></td>
                        <td>
                            @if($item['country']->flag_url ?? false)
                                <img src="{{ $item['country']->flag_url }}" alt="{{ $item['country_name'] }}" style="width: 24px; height: 18px;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $item['country']->region ?? '-' }}</td>
                        <td>{{ $item['country']->currency_code ?? '-' }}</td>
                        <td>
                            @if($item['risk_score'])
                                <strong>{{ number_format($item['risk_score']['total_score'], 1) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item['risk_score'])
                                @php
                                    $score = $item['risk_score']['total_score'];
                                    $riskLevel = '';
                                    // Consistent risk level classification: 0-25 low, 26-50 medium, 51-75 high, 76-100 critical
                                    if ($score >= 76) {
                                        $badgeClass = 'bg-danger';
                                        $label = 'Critical';
                                        $riskLevel = 'critical';
                                    } elseif ($score >= 51) {
                                        $badgeClass = 'bg-warning text-dark';
                                        $label = 'High';
                                        $riskLevel = 'high';
                                    } elseif ($score >= 26) {
                                        $badgeClass = 'bg-info';
                                        $label = 'Medium';
                                        $riskLevel = 'medium';
                                    } else {
                                        $badgeClass = 'bg-success';
                                        $label = 'Low';
                                        $riskLevel = 'low';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                            @else
                                <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if($item['weather'])
                                <i class="fas fa-temperature-half"></i> {{ number_format($item['weather']->temperature, 1) }}°C
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item['exchange_rate'])
                                {{ number_format($item['exchange_rate'], 4) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $item['updated_at']->diffForHumans() }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-primary viewDashboardBtn" data-country-code="{{ $item['country_code'] }}" title="View Dashboard">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-success refreshBtn" data-id="{{ $item['id'] }}" title="Refresh Data">
                                <i class="fas fa-rotate"></i>
                            </button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="{{ $item['id'] }}" data-country="{{ $item['country_name'] }}" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
