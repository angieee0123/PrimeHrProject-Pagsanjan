import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class PayslipListScreen extends StatefulWidget {
  const PayslipListScreen({super.key});

  @override
  State<PayslipListScreen> createState() => _PayslipListScreenState();
}

class _PayslipListScreenState extends State<PayslipListScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Payslips',
          style: GoogleFonts.inter(
            fontWeight: FontWeight.w700,
            color: const Color(0xFF0F172A),
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 1,
        foregroundColor: const Color(0xFF1E3A8A),
      ),
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search payslips...',
                prefixIcon: const Icon(Icons.search),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey.shade300),
                ),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
          ),
          // Payslips List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              itemCount: MockData.payslips.length,
              itemBuilder: (context, index) {
                final payslip = MockData.payslips[index];
                return RecordCard(
                  title: 'Payslip - ${payslip.period.toString().split(' ')[0]}',
                  subtitle:
                      'Pay Date: ${payslip.payDate.toString().split(' ')[0]}',
                  details: [
                    {
                      'label': 'Gross Pay',
                      'value': '₱${payslip.grossPay.toStringAsFixed(2)}',
                    },
                    {
                      'label': 'Deductions',
                      'value': '₱${payslip.deductions.toStringAsFixed(2)}',
                    },
                    {
                      'label': 'Net Pay',
                      'value': '₱${payslip.netPay.toStringAsFixed(2)}',
                    },
                  ],
                  badge: StatusBadgeData(
                    label: payslip.status,
                    status: payslip.status.toLowerCase(),
                  ),
                  actions: [
                    ActionButton(
                      label: 'View',
                      icon: Icons.visibility,
                      onTap: () => _viewPayslipDetails(context, payslip),
                    ),
                    ActionButton(
                      label: 'Download',
                      icon: Icons.download,
                      onTap: () {},
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  void _viewPayslipDetails(BuildContext context, dynamic payslip) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Payslip Details',
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _buildDetailRow(
                'Period',
                payslip.period.toString().split(' ')[0],
              ),
              _buildDetailRow(
                'Basic Pay',
                '₱${payslip.basicPay.toStringAsFixed(2)}',
              ),
              _buildDetailRow(
                'Overtime',
                '₱${payslip.overtimePay.toStringAsFixed(2)}',
              ),
              _buildDetailRow(
                'Allowances',
                '₱${payslip.allowances.toStringAsFixed(2)}',
              ),
              _buildDetailRow(
                'Gross Pay',
                '₱${payslip.grossPay.toStringAsFixed(2)}',
                isBold: true,
              ),
              const Divider(height: 16),
              Text(
                'Deductions',
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 8),
              ...payslip.deductionDetails.entries.map(
                (e) => _buildDetailRow(e.key, '₱${e.value.toStringAsFixed(2)}'),
              ),
              const Divider(height: 16),
              _buildDetailRow(
                'Total Deductions',
                '₱${payslip.deductions.toStringAsFixed(2)}',
                isBold: true,
              ),
              _buildDetailRow(
                'Net Pay',
                '₱${payslip.netPay.toStringAsFixed(2)}',
                isBold: true,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: isBold ? FontWeight.w600 : FontWeight.w500,
              color: isBold ? const Color(0xFF0F172A) : Colors.grey.shade600,
            ),
          ),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: isBold ? FontWeight.w700 : FontWeight.w600,
              color: isBold ? const Color(0xFF1E3A8A) : const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }
}
