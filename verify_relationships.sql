-- Verify User → Employee → Employment Details Relationship

SELECT 
    u.id as user_id,
    u.email as login_email,
    u.role as user_role,
    u.status as user_status,
    u.employee_id,
    e.employee_id as emp_code,
    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
    e.email as employee_email,
    ed.employment_status,
    ed.department_id,
    d.name as department_name
FROM users u
LEFT JOIN employees e ON u.employee_id = e.id
LEFT JOIN employment_details ed ON e.id = ed.employee_id
LEFT JOIN departments d ON ed.department_id = d.id
WHERE u.status = 'Active'
ORDER BY u.id;

-- Expected Results:
-- user_id | login_email              | employee_name           | employment_status | Should Route To
-- 1       | admin@gmail.com          | System Administrator    | Permanent         | Admin Dashboard (admin check)
-- 6       | joborder@gmail.com       | Jeremy Reyes Pogi       | Permanent         | Permanent Dashboard ✅
-- 7       | permanent@gmail.com      | Juan Reyes Dela Cruz    | Permanent         | Permanent Dashboard ✅
-- 8       | ana.ramos@primehr.com    | Ana Garcia Ramos        | Permanent         | Permanent Dashboard ✅
-- 9       | pedro.santos@primehr.com | Pedro Mendoza Santos    | Permanent         | Permanent Dashboard ✅
