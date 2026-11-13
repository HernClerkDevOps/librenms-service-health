<div id="service-health-{{ $id }}">
    <div class="clearfix" style="margin-bottom:8px;">
        <button type="button" class="btn btn-default btn-xs pull-right"
                onclick="widget_reload({{ (int) $id }}, 'hernando-service-health', true)">
            <i class="fa fa-refresh" aria-hidden="true"></i> Refresh
        </button>
        <span class="text-muted">Stale after {{ $staleAfterMinutes }} minutes</span>
    </div>

    @if($missingCount > 0)
        <div class="alert alert-warning" style="padding:8px;">
            {{ $missingCount }} configured service(s) are unavailable or no longer accessible.
        </div>
    @endif

    @if($rows->isEmpty())
        <div class="alert alert-info">No accessible configured services were found.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-condensed" style="margin-bottom:0;">
                <thead>
                <tr>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Last poll</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>
                            <a href="{{ $row['url'] }}" title="Open this device's services">
                                {{ $row['name'] }}
                            </a>
                            <div class="small text-muted">
                                {{ $row['device'] }}@if($row['type']), {{ $row['type'] }}@endif
                            </div>
                            @if($row['message'])
                                <div class="small" title="{{ $row['message'] }}">
                                    {{ \Illuminate\Support\Str::limit($row['message'], 100) }}
                                </div>
                            @endif
                        </td>
                        <td><span class="label label-{{ $row['statusClass'] }}">{{ $row['status'] }}</span></td>
                        <td>{{ $row['responseTime'] ?? 'N/A' }}</td>
                        <td title="{{ $row['lastPoll'] ?? 'No poll recorded' }}">{{ $row['lastPollAge'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
