{{-- Welcome Message Component --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Welcome to Health Audit System</h4>
                @role('Super Admin')
                <p>As a Super Admin, you have full access to manage the entire health audit system. You can create users, assign roles, manage templates, and oversee all audit activities.</p>
                @endrole
                @role('Admin')
                <p>As an Admin, you can manage audit content, templates, and help coordinate audit activities across the system.</p>
                @endrole
                @role('Audit Manager')
                <p>As an Audit Manager, you can create new audits, assign auditors, and generate comprehensive reports from audit data.</p>
                @endrole
                @role('Auditor')
                <p>As an Auditor, you can access your assigned audits and submit responses. Use the audit codes provided to you to access specific audits.</p>
                @endrole
            </div>
        </div>
    </div>
</div>
