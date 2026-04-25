# HR Chatbot Update Examples

## Update Employee Status
```
✅ "Update status of Juan from active to inactive"
✅ "Change employment status of employee ID 1000 to Permanent"
✅ "I-update ang status ni Maria to Active"
✅ "Set status of Pedro to Contractual"
```

## Update Email Address
```
✅ "Update employee ID 1000 email to newemail@gmail.com"
✅ "Change email of Juan to juan.delacruz@company.com"
✅ "I-update ang email ni Maria to maria123@yahoo.com"
✅ "Set email for employee 2000 to admin@primehr.com"
```

## Update Position
```
✅ "Update position of Juan to Manager"
✅ "Change employee ID 1000 position to Senior Developer"
✅ "I-update ang position ni Maria to HR Supervisor"
✅ "Set role of Pedro to Administrative Assistant"
```

## Update Mobile Number
```
✅ "Update mobile of Juan to 09171234567"
✅ "Change phone number of employee ID 1000 to +639281234567"
✅ "I-update ang contact ni Maria to 09123456789"
✅ "Set mobile for Pedro to 0917-555-1234"
```

## Update Civil Status
```
✅ "Update civil status of Juan to Married"
✅ "Change marital status of employee ID 1000 to Single"
✅ "I-update ang civil status ni Maria to Widowed"
✅ "Set civil status of Pedro to Separated"
```

## Mixed Language (Taglish)
```
✅ "I-update ang status ng employee ID 1000 from Active to Inactive"
✅ "Baguhin ang email ni Juan to newemail@gmail.com"
✅ "Palitan ang position ng employee 2000 to Manager"
✅ "Ayusin ang mobile number ni Maria to 09171234567"
```

## Expected Responses

### Success Response:
```
✅ Successfully updated!

Employee: 1000
Field: employment_status
Old value: Active
New value: Inactive
```

### Validation Error:
```
❌ Update failed: Invalid status. Use: Active, Inactive, Permanent, Contractual, Probationary
```

### Employee Not Found:
```
❌ Update failed: Employee 'Juan' not found
```

### Invalid Email:
```
❌ Update failed: Invalid email format
```

## Supported Fields
- **email** - Email address (must contain @ and .)
- **employment_status** - Active, Inactive, Permanent, Contractual, Probationary
- **position** - Job title/role
- **mobile_number** - Contact number (min 10 digits)
- **civil_status** - Single, Married, Widowed, Divorced, Separated

## Notes
- You can use employee ID or first name to identify employees
- System validates all inputs before updating
- Shows old and new values for transparency
- Supports Filipino, English, and Taglish commands
