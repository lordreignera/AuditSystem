<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <!-- Profile Section -->
    <li class="nav-item profile">
      <div class="profile-desc">
        <div class="profile-pic">
          <div class="count-indicator">
            @if(Auth::check() && Auth::user()->profile_photo_path)
              <img class="img-xs rounded-circle" src="{{ asset('user_images/' . Auth::user()->profile_photo_path) }}" alt="Profile Picture">
            @else
              <img class="img-xs rounded-circle" src="admin/assets/images/faces/face15.jpg" alt="Default Profile Picture">
            @endif
            <span class="count bg-success"></span>
          </div>
          <div class="profile-name">
            <h5 class="mb-0 font-weight-normal">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</h5>
            <span>{{ Auth::check() && Auth::user()->roles->isNotEmpty() ? Auth::user()->roles->first()->name : 'No Role Assigned' }}</span>
          </div>
        </div>
      </div>
    </li>

    <li class="nav-item nav-category">
      <span class="nav-link">Navigation</span>
    </li>

    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ url('home') }}">
        <span class="menu-icon">
          <i class="mdi mdi-speedometer"></i>
        </span>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <!-- Quick Access for Auditors -->
    @role('Auditor')
    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ url('my-audits') }}">
        <span class="menu-icon">
          <i class="mdi mdi-clipboard-text"></i>
        </span>
        <span class="menu-title">My Assigned Audits</span>
      </a>
    </li>

    <li class="nav-item menu-items">
      <a class="nav-link" href="{{ url('audit-code') }}">
        <span class="menu-icon">
          <i class="mdi mdi-key"></i>
        </span>
        <span class="menu-title">Enter Audit Code</span>
      </a>
    </li>
    @endrole

    <!-- Countries -->
    @can('manage countries')
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#countries" aria-expanded="false" aria-controls="countries">
        <span class="menu-icon">
          <i class="mdi mdi-earth"></i>
        </span>
        <span class="menu-title">Countries</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="countries">
        <ul class="nav flex-column sub-menu">
          @can('view countries')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.countries.index') }}"><i class="mdi mdi-view-list"></i> All Countries</a></li>
          @endcan
          @can('create countries')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.countries.create') }}"><i class="mdi mdi-earth-plus"></i> Add Country</a></li>
          @endcan
        </ul>
      </div>
    </li>
    @endcan

    @can('manage countries')
    <li class="nav-item nav-category">
      <span class="nav-link">System Data</span>
    </li>
    @endcan

    <!-- Audits & Reviews -->
    @can('view audits')
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#projects" aria-expanded="false" aria-controls="projects">
        <span class="menu-icon"><i class="mdi mdi-file-document-box"></i></span>
        <span class="menu-title">Audits & Reviews</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="projects">
        <ul class="nav flex-column sub-menu">
          @can('view audits')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.audits.index') }}"><i class="mdi mdi-view-list"></i> All Audits</a></li>
          @endcan
          @can('create audits')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.audits.create') }}"><i class="mdi mdi-plus-circle"></i> Create New Audit</a></li>
          @endcan
          <hr>
          @can('view audits')
          @php
            $reviewTypeLinks = [
              ['name' => 'National Reviews', 'slug' => 'National'],
              ['name' => 'Provincial Reviews', 'slug' => 'Province/region'],
              ['name' => 'District Reviews', 'slug' => 'District'],
              ['name' => 'Health Facility Reviews', 'slug' => 'Health Facility']
            ];
          @endphp
          @foreach($reviewTypeLinks as $link)
            @php
              $reviewType = App\Models\ReviewType::where('name', $link['slug'])->first();
            @endphp
            @if($reviewType)
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.review-types-crud.show', $reviewType->id) }}">{{ $link['name'] }}</a></li>
            @else
              <li class="nav-item"><a class="nav-link" href="{{ route('admin.review-types-crud.index') }}">{{ $link['name'] }}</a></li>
            @endif
          @endforeach
          @endcan
          @can('manage review types')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.review-types.index') }}">Manage Review Types</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.review-types-crud.index') }}">Templates & Questions</a></li>
          @endcan
        </ul>
      </div>
    </li>
    @endcan

    <!-- Reports -->
    @can('view reports')
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#reports" aria-expanded="false" aria-controls="reports">
        <span class="menu-icon"><i class="mdi mdi-chart-bar"></i></span>
        <span class="menu-title">Reports</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="reports">
        <ul class="nav flex-column sub-menu">
          @can('view reports')
          <li class="nav-item"><a class="nav-link" href="{{ url('sales_report') }}">Audit Report</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('monthly_report') }}">Monthly Audit Report</a></li>
          @endcan
          @can('generate reports')
          <li class="nav-item"><a class="nav-link" href="{{ url('generate_reports') }}">Generate Custom Report</a></li>
          @endcan
          @can('export reports')
          <li class="nav-item"><a class="nav-link" href="{{ url('export_reports') }}">Export Reports</a></li>
          @endcan
        </ul>
      </div>
    </li>
    @endcan

    <!-- Access Control -->
    @can('manage users')
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#accounts" aria-expanded="false" aria-controls="accounts">
        <span class="menu-icon"><i class="mdi mdi-account-key"></i></span>
        <span class="menu-title">User Management</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="accounts">
        <ul class="nav flex-column sub-menu">
          @can('manage users')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}"><i class="mdi mdi-account-multiple"></i> Manage Users</a></li>
          @endcan
          @can('manage roles')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.roles.index') }}"><i class="mdi mdi-shield-account"></i> Manage Roles</a></li>
          @endcan
          @can('manage permissions')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.permissions.index') }}"><i class="mdi mdi-key"></i> Manage Permissions</a></li>
          @endcan
          <hr>
          @can('create users')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.create') }}"><i class="mdi mdi-account-plus"></i> Add New User</a></li>
          @endcan
          @can('create roles')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.roles.create') }}"><i class="mdi mdi-shield-plus"></i> Create Role</a></li>
          @endcan
        </ul>
      </div>
    </li>
    @endcan

    <!-- Templates by Review Type (ONLY show default templates, never audit-specific copies) -->
    @can('view templates')
    <li class="nav-item menu-items">
      <a class="nav-link" data-bs-toggle="collapse" href="#templatesByType" aria-expanded="false" aria-controls="templatesByType">
        <span class="menu-icon"><i class="mdi mdi-file-document-box-multiple"></i></span>
        <span class="menu-title">Audit Templates</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="templatesByType">
        <ul class="nav flex-column sub-menu">
          @can('manage templates')
          <li class="nav-item"><a class="nav-link" href="{{ url('templates/create') }}">Create Template</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('templates/manage') }}">Manage Templates</a></li>
          <hr>
          @endcan
          @php
            try {
              $reviewTypes = \App\Models\ReviewType::where('is_active', true)->get();
            } catch (\Exception $e) {
              $reviewTypes = collect();
            }
          @endphp
          @foreach($reviewTypes as $type)
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#templates-{{ $type->id }}" aria-expanded="false" aria-controls="templates-{{ $type->id }}">
                <span class="menu-title">{{ $type->name }}</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="templates-{{ $type->id }}">
                <ul class="nav flex-column sub-menu">
                  @php
                    $defaultTemplates = \App\Models\Template::where('review_type_id', $type->id)
                      ->where('is_default', true)
                      ->whereNull('audit_id')
                      ->get();
                  @endphp
                  @if($defaultTemplates->count() > 0)
                    @foreach($defaultTemplates as $template)
                      <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.review-types-crud.create-audit', [$type->id, $template->id]) }}">
                          {{ $template->name }}
                        </a>
                      </li>
                    @endforeach
                  @else
                    <li class="nav-item"><a class="nav-link text-muted" href="#">No default templates yet</a></li>
                  @endif
                </ul>
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    </li>
    @endcan

  </ul>
</nav>