{{-- Quick Actions Component --}}
<div class="col-md-4 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Quick Actions</h4>
            <div class="list-wrapper">
                <ul class="d-flex flex-column-reverse todo-list">
                    @foreach($actions as $action)
                        @if(isset($action['permission']) && !auth()->user()->can($action['permission']))
                            @continue
                        @endif
                        
                        @if(isset($action['role']) && !auth()->user()->hasRole($action['role']))
                            @continue
                        @endif
                        
                        <li class="{{ $loop->first ? '' : 'mt-2' }}">
                            <div class="form-check">
                                <a href="{{ $action['url'] }}"
                                   class="btn btn-{{ $action['class'] ?? 'primary' }} btn-sm text-white w-100"
                                   style="background: {{ $action['class'] === 'primary' ? '#2563eb' : ($action['class'] === 'success' ? '#22c55e' : ($action['class'] === 'info' ? '#0ea5e9' : ($action['class'] === 'warning' ? '#f59e42' : '#64748b'))) }}; border: none; display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
                                    @if(isset($action['icon']))
                                        <i class="mdi {{ $action['icon'] }}" style="color: #fff;"></i>
                                    @endif
                                    <span style="color: #fff; font-weight: 500;">{{ $action['title'] }}</span>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
