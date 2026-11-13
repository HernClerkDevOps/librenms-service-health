@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">Widget title</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}"
               placeholder="Service Health" value="{{ $title }}">
    </div>

    <div class="form-group">
        <label for="service-ids-{{ $id }}" class="control-label">Services</label>
        <select class="form-control" id="service-ids-{{ $id }}" name="service_ids[]" multiple required
                data-placeholder="Select one or more services">
            @foreach($services as $service)
                <option value="{{ $service->service_id }}"
                        @selected(in_array((int) $service->service_id, $service_ids, true))>
                    {{ $service->device?->display ?? $service->device?->hostname ?? 'Unknown device' }}:
                    {{ $service->service_name ?: ($service->service_desc ?: $service->service_type) }}
                </option>
            @endforeach
        </select>
        <p class="help-block">Only services you can access are listed.</p>
    </div>

    <div class="form-group">
        <label for="stale-after-{{ $id }}" class="control-label">Mark stale after (minutes)</label>
        <input type="number" step="1" min="1" max="1440" class="form-control"
               name="stale_after_minutes" id="stale-after-{{ $id }}"
               value="{{ $stale_after_minutes }}" required>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $('#service-ids-{{ $id }}').select2({width: '100%'});
    </script>
@endsection
