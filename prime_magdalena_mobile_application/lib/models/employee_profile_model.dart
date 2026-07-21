/// Employee profile returned by GET /api/mobile/profile
class EmployeeProfile {
  final ProfilePersonal personal;
  final ProfileEmployment employment;
  final ProfileGovernmentIds governmentIds;
  final ProfileEmergency emergency;

  const EmployeeProfile({
    required this.personal,
    required this.employment,
    required this.governmentIds,
    required this.emergency,
  });

  factory EmployeeProfile.fromJson(Map<String, dynamic> json) {
    return EmployeeProfile(
      personal: ProfilePersonal.fromJson(
        json['personal'] as Map<String, dynamic>? ?? {},
      ),
      employment: ProfileEmployment.fromJson(
        json['employment'] as Map<String, dynamic>? ?? {},
      ),
      governmentIds: ProfileGovernmentIds.fromJson(
        json['government_ids'] as Map<String, dynamic>? ?? {},
      ),
      emergency: ProfileEmergency.fromJson(
        json['emergency'] as Map<String, dynamic>? ?? {},
      ),
    );
  }

  String get initials {
    final first = personal.firstName.trim();
    final last = personal.lastName.trim();
    if (first.isNotEmpty && last.isNotEmpty) {
      return '${first[0]}${last[0]}'.toUpperCase();
    }
    final full = personal.fullName.trim();
    if (full.isEmpty) return 'E';
    final parts = full.split(RegExp(r'\s+'));
    if (parts.length >= 2) {
      return '${parts.first[0]}${parts.last[0]}'.toUpperCase();
    }
    return full[0].toUpperCase();
  }
}

class ProfilePersonal {
  final String firstName;
  final String? middleName;
  final String lastName;
  final String? suffix;
  final String fullName;
  final String? sex;
  final String? birthDate;
  final String? civilStatus;
  final String? email;
  final String? phone;
  final String? address;

  const ProfilePersonal({
    required this.firstName,
    this.middleName,
    required this.lastName,
    this.suffix,
    required this.fullName,
    this.sex,
    this.birthDate,
    this.civilStatus,
    this.email,
    this.phone,
    this.address,
  });

  factory ProfilePersonal.fromJson(Map<String, dynamic> json) {
    return ProfilePersonal(
      firstName: json['first_name']?.toString() ?? '',
      middleName: json['middle_name']?.toString(),
      lastName: json['last_name']?.toString() ?? '',
      suffix: json['suffix']?.toString(),
      fullName: json['full_name']?.toString() ?? '',
      sex: json['sex']?.toString(),
      birthDate: json['birth_date']?.toString(),
      civilStatus: json['civil_status']?.toString(),
      email: json['email']?.toString(),
      phone: json['phone']?.toString(),
      address: json['address']?.toString(),
    );
  }
}

class ProfileEmployment {
  final String? employeeId;
  final String? employmentStatus;
  final String? designation;
  final String? department;
  final String? appointmentDate;
  final String? salaryGrade;
  final String? stepIncrement;
  final double? monthlyRate;
  final String? userStatus;

  const ProfileEmployment({
    this.employeeId,
    this.employmentStatus,
    this.designation,
    this.department,
    this.appointmentDate,
    this.salaryGrade,
    this.stepIncrement,
    this.monthlyRate,
    this.userStatus,
  });

  factory ProfileEmployment.fromJson(Map<String, dynamic> json) {
    final rate = json['monthly_rate'];
    return ProfileEmployment(
      employeeId: json['employee_id']?.toString(),
      employmentStatus: json['employment_status']?.toString(),
      designation: json['designation']?.toString(),
      department: json['department']?.toString(),
      appointmentDate: json['appointment_date']?.toString(),
      salaryGrade: json['salary_grade']?.toString(),
      stepIncrement: json['step_increment']?.toString(),
      monthlyRate: rate is num
          ? rate.toDouble()
          : double.tryParse(rate?.toString() ?? ''),
      userStatus: json['user_status']?.toString(),
    );
  }
}

class ProfileGovernmentIds {
  final String? gsisNo;
  final String? philhealthNo;
  final String? pagibigNo;
  final String? tinNo;
  final String? licenseNo;

  const ProfileGovernmentIds({
    this.gsisNo,
    this.philhealthNo,
    this.pagibigNo,
    this.tinNo,
    this.licenseNo,
  });

  factory ProfileGovernmentIds.fromJson(Map<String, dynamic> json) {
    return ProfileGovernmentIds(
      gsisNo: json['gsis_no']?.toString(),
      philhealthNo: json['philhealth_no']?.toString(),
      pagibigNo: json['pagibig_no']?.toString(),
      tinNo: json['tin_no']?.toString(),
      licenseNo: json['license_no']?.toString(),
    );
  }
}

class ProfileEmergency {
  final String? contactPerson;
  final String? phone;
  final String? address;

  const ProfileEmergency({
    this.contactPerson,
    this.phone,
    this.address,
  });

  factory ProfileEmergency.fromJson(Map<String, dynamic> json) {
    return ProfileEmergency(
      contactPerson: json['contact_person']?.toString(),
      phone: json['phone']?.toString(),
      address: json['address']?.toString(),
    );
  }
}
