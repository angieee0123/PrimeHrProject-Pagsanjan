/// Authentication Models for Mobile App
library;

/// User Model
class UserModel {
  final int id;
  final String name;
  final String email;
  final String? username;
  final String role;
  final int? employeeId;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.username,
    required this.role,
    this.employeeId,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      username: json['username']?.toString(),
      role: json['role']?.toString() ?? 'employee',
      employeeId: json['employee_id'] as int?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'username': username,
      'role': role,
      'employee_id': employeeId,
    };
  }
}

/// Employee Model (simplified for auth)
class EmployeeModel {
  final int id;
  final String firstName;
  final String? middleName;
  final String lastName;
  final String? suffix;
  final String fullName;
  final String? employmentStatus;
  final String? department;
  final String? designation;

  EmployeeModel({
    required this.id,
    required this.firstName,
    this.middleName,
    required this.lastName,
    this.suffix,
    required this.fullName,
    this.employmentStatus,
    this.department,
    this.designation,
  });

  factory EmployeeModel.fromJson(Map<String, dynamic> json) {
    return EmployeeModel(
      id: json['id'] as int,
      firstName: json['first_name']?.toString() ?? '',
      middleName: json['middle_name']?.toString(),
      lastName: json['last_name']?.toString() ?? '',
      suffix: json['suffix']?.toString(),
      fullName: json['full_name']?.toString() ?? '',
      employmentStatus: json['employment_status']?.toString(),
      department: json['department']?.toString(),
      designation: json['designation']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'first_name': firstName,
      'middle_name': middleName,
      'last_name': lastName,
      'suffix': suffix,
      'full_name': fullName,
      'employment_status': employmentStatus,
      'department': department,
      'designation': designation,
    };
  }
}

/// Login Response Model
class LoginResponse {
  final String token;
  final UserModel user;
  final EmployeeModel? employee;

  LoginResponse({
    required this.token,
    required this.user,
    this.employee,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>;
    return LoginResponse(
      token: data['token']?.toString() ?? '',
      user: UserModel.fromJson(data['user'] as Map<String, dynamic>),
      employee: data['employee'] != null
          ? EmployeeModel.fromJson(data['employee'] as Map<String, dynamic>)
          : null,
    );
  }
}
