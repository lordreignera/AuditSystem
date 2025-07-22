{{-- Recent Activities Table Component --}}
<div class="col-md-8 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <p class="card-title">{{ $title }}</p>
                <a href="{{ $viewAllUrl ?? '#' }}" class="text-info">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            @foreach($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                @foreach($row as $key => $cell)
                                    @if(is_array($cell) && isset($cell['text'], $cell['class']))
                                        <td><label class="badge badge-{{ $cell['class'] }}">{{ $cell['text'] }}</label></td>
                                    @elseif(is_array($cell) && isset($cell['url'], $cell['text'], $cell['class']))
                                        <td><a href="{{ $cell['url'] }}" class="btn btn-sm btn-{{ $cell['class'] }}">{{ $cell['text'] }}</a></td>
                                    @else
                                        <td>{{ $cell }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($headers) }}" class="text-center text-muted">
                                    {{ $emptyMessage ?? 'No data available' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
