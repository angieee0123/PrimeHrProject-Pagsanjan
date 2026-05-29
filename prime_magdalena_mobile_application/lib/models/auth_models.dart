/// Authentication Models for Mobile App

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
      name: json['name'] as String,
      email: json['email'] as String,
      username: json['username'] as String?,
      role: json['role'] as String,
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
      firstName: json['first_name'] as String,
      middleName: json['middle_name'] as String?,
      lastName: json['last_name'] as String,
      suffix: json['suffix'] as String?,
      fullName: json['full_name'] as String,
      employmentStatus: json['employment_status'] as String?,
      department: json['department'] as String?,
      designation: json['designation'] as String?,
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
      token: data['token'] as String,
      user: UserModel.fromJson(data['user'] as Map<String, dynamic>),
      employee: data['employee'] != null
          ? EmployeeModel.fromJson(data['employee'] as Map<String, dynamic>)
          : null,
    );
  }
}
