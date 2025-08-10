# 🧪 **Testing the Auditor Registration & Assignment Feature**

## 🚀 **Quick Start Testing Guide**

### **1. Access the Registration Page**
Navigate to: `http://localhost/AuditSystem/register` (or your local URL)

### **2. Test Auditor Registration Flow**

#### **Step 1: Fill Basic Information**
- Name: `Test Auditor`
- Email: `auditor@test.com`
- Password: `password123`
- Confirm Password: `password123`

#### **Step 2: Role Selection Test**
1. **Select "Auditor" role** from dropdown
2. **Verify**: Audit assignment section appears automatically
3. **Verify**: List of available audits is displayed with details:
   - Audit name
   - Country name
   - Due date

#### **Step 3: Audit Assignment**
1. **Select an audit** from the dropdown
2. **Verify**: Required field validation is active
3. **Submit registration**

#### **Step 4: Verify Registration Success**
1. **Check**: User is created and redirected to login
2. **Login** with the new credentials
3. **Verify**: User has "Auditor" role
4. **Verify**: User is assigned to selected audit

### **3. Test Non-Auditor Registration Flow**

#### **Step 1: Fill Basic Information**
- Name: `Test Manager`
- Email: `manager@test.com`
- Password: `password123`
- Confirm Password: `password123`

#### **Step 2: Role Selection Test**
1. **Select any role** except "Auditor" (e.g., "Admin", "Audit Manager")
2. **Verify**: Audit assignment section remains hidden
3. **Submit registration**

#### **Step 3: Verify Registration Success**
1. **Check**: User is created with selected role
2. **Login** and verify correct role assignment
3. **Verify**: No audit assignments created

## 🎯 **Dashboard Access Testing**

### **For Auditor Users:**
After logging in as an auditor:

1. **Dashboard Test**:
   - Go to `/admin/dashboard`
   - **Verify**: "My Assigned Audits" section shows assigned audit
   - **Verify**: Recent audits table shows only assigned audit

2. **Audit Access Test**:
   - Go to `/admin/audits`
   - **Verify**: Only sees assigned audit in list
   - Click on assigned audit
   - **Verify**: Can access audit dashboard

3. **Access Control Test**:
   - Try to access different audit ID manually
   - **Verify**: Gets 403 Forbidden error

### **For Non-Auditor Users:**
After logging in as non-auditor:

1. **Dashboard Test**:
   - **Verify**: Sees all audits based on permissions
   - **Verify**: No audit assignment restrictions

2. **Full Access Test**:
   - **Verify**: Can access all audits they have permissions for

## 🐛 **Troubleshooting Common Issues**

### **Issue: Registration Form Not Showing Roles**
**Cause**: No roles exist in database
**Fix**: Create roles using admin panel or seeders

### **Issue: No Audits Available for Assignment**
**Cause**: No active audits in database
**Fix**: Create audits using admin panel

### **Issue: Audit Assignment Section Not Appearing**
**Cause**: JavaScript not loading
**Fix**: Check browser console for errors

### **Issue: 500 Error During Registration**
**Cause**: Missing role or audit validation
**Fix**: Check Laravel logs at `storage/logs/laravel.log`

## 🔍 **Database Verification**

### **Check User Creation:**
```sql
SELECT u.id, u.name, u.email, r.name as role_name 
FROM users u 
JOIN model_has_roles mhr ON u.id = mhr.model_id 
JOIN roles r ON mhr.role_id = r.id 
WHERE u.email = 'auditor@test.com';
```

### **Check Audit Assignment:**
```sql
SELECT u.name, a.name as audit_name, uaa.assigned_at 
FROM users u 
JOIN user_audit_assignments uaa ON u.id = uaa.user_id 
JOIN audits a ON uaa.audit_id = a.id 
WHERE u.email = 'auditor@test.com';
```

## 📊 **Expected Results Summary**

### **✅ What Should Work:**

1. **Registration Form**:
   - Role dropdown shows all roles except "Super Admin"
   - Audit section appears/hides based on role selection
   - Form validation works correctly

2. **User Creation**:
   - User record created with correct details
   - Role assigned automatically
   - Audit assignment created for auditors

3. **Dashboard Access**:
   - Auditors see only assigned audits
   - Non-auditors see all permitted audits
   - Proper access control enforced

4. **Navigation**:
   - Role-based menu filtering
   - Audit-specific access restrictions
   - Proper 403 errors for unauthorized access

### **🚫 What Should Be Restricted:**

1. **Auditor Limitations**:
   - Cannot see unassigned audits
   - Cannot access other audit dashboards
   - Limited to assigned audit only

2. **Role Restrictions**:
   - Cannot select "Super Admin" during registration
   - Must select a role (required field)
   - Auditors must select an audit

## 🎉 **Success Criteria**

The implementation is successful if:

✅ Users can register with role selection
✅ Auditors get audit assignment during registration  
✅ Dashboard shows role-appropriate content
✅ Access control works as expected
✅ No errors during registration flow
✅ Database relationships are created correctly

## 📞 **Support Information**

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database connections
3. Clear caches: `php artisan cache:clear`
4. Check JavaScript console for client-side errors

**Implementation Status: ✅ READY FOR TESTING**
