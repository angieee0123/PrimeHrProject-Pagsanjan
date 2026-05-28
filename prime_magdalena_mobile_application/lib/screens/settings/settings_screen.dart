import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _payslip = true;
  bool _leave = true;
  bool _training = true;
  bool _announcements = true;

  @override
  Widget build(BuildContext context) {
    final employee = MockData.currentEmployee;

    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFF0B044D),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 26,
                  backgroundColor: Colors.white.withValues(alpha: 0.18),
                  child: Text(
                    employee.initials,
                    style: GoogleFonts.inter(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        employee.fullName,
                        style: GoogleFonts.inter(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        employee.id,
                        style: GoogleFonts.inter(
                          color: Colors.white70,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          _sectionTitle('Personal Information'),
          TextField(
            decoration: _inputDecoration('Email'),
            controller: TextEditingController(text: employee.email),
          ),
          const SizedBox(height: 12),
          TextField(
            decoration: _inputDecoration('Contact Number'),
            controller: TextEditingController(text: employee.phone),
          ),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: () {}, child: const Text('Save Changes')),
          const SizedBox(height: 20),
          _sectionTitle('Security'),
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const Icon(Icons.lock_outline),
            title: const Text('Change Password'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {},
          ),
          const SizedBox(height: 12),
          _sectionTitle('Notifications'),
          _switchTile('Payslip Available', _payslip, (value) {
            setState(() => _payslip = value);
          }),
          _switchTile('Leave Status Update', _leave, (value) {
            setState(() => _leave = value);
          }),
          _switchTile('Training Reminder', _training, (value) {
            setState(() => _training = value);
          }),
          _switchTile('System Announcements', _announcements, (value) {
            setState(() => _announcements = value);
          }),
        ],
      ),
    );
  }

  Widget _sectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Text(
        title,
        style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w700),
      ),
    );
  }

  Widget _switchTile(String title, bool value, ValueChanged<bool> onChanged) {
    return SwitchListTile(
      contentPadding: EdgeInsets.zero,
      title: Text(title),
      value: value,
      activeThumbColor: const Color(0xFF1E3A8A),
      onChanged: onChanged,
    );
  }

  InputDecoration _inputDecoration(String label) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    );
  }
}
