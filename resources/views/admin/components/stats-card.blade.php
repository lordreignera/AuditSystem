{{-- Reusable Statistics Card Component --}}
<div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-9">
                    <div class="d-flex align-items-center align-self-start">
                        <h3 class="mb-0">{{ $value }}</h3>
                    </div>
                    <h6 class="text-muted font-weight-normal">{{ $title }}</h6>
                </div>
                <div class="col-3">
                    <div class="icon icon-box-{{ $color }}">
                        <span class="mdi {{ $icon }} icon-item"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
