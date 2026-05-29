import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class LeaveManagementScreen extends StatefulWidget {
  const LeaveManagementScreen({super.key});

  @override
  State<LeaveManagementScreen> createState() => _LeaveManagementScreenState();
}

class _LeaveManagementScreenState extends State<LeaveManagementScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Leave & Benefits',
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
          // Tabs
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabController,
              tabs: const [
                Tab(text: 'Requests'),
                Tab(text: 'Credits'),
              ],
              labelColor: const Color(0xFF1E3A8A),
              unselectedLabelColor: Colors.grey.shade600,
              indicatorColor: const Color(0xFF1E3A8A),
            ),
          ),
          // Tab Content
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                // Requests Tab
                _buildRequestsTab(),
                // Credits Tab
                _buildCreditsTab(),
              ],
            ),
          ),
        ],
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: FloatingActionButton.extended(
          onPressed: () => _showFileLeaveDialog(context),
          backgroundColor: const Color(0xFF1E3A8A),
          icon: const Icon(Icons.add_rounded, size: 22),
          label: Text(
            'File Leave',
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
    );
  }

  Widget _buildRequestsTab() {
    return ListView.builder(
      padding: const EdgeInsets.only(
        left: 16,
        right: 16,
        top: 12,
        bottom: 88, // Extra padding for FAB
      ),
      itemCount: MockData.leaveRequests.length,
      itemBuilder: (context, index) {
        final request = MockData.leaveRequests[index];
        final days = request.endDate.difference(request.startDate).inDays + 1;
        return RecordCard(
          title: request.leaveType,
          subtitle:
              '${request.startDate.toString().split(' ')[0]} - ${request.endDate.toString().split(' ')[0]}',
          details: [
            {'label': 'Days', 'value': '$days day${days > 1 ? 's' : ''}'},
            {'label': 'Reason', 'value': request.reason},
          ],
          badge: StatusBadgeData(
            label: request.status,
            status: request.status.toLowerCase(),
          ),
          actions: [
            if (request.status.toLowerCase() == 'pending')
              ActionButton(label: 'Cancel', icon: Icons.close, onTap: () {}),
            ActionButton(
              label: 'Details',
              icon: Icons.info_outline,
              onTap: () => _viewLeaveDetails(context, request),
            ),
          ],
        );
      },
    );
  }

  Widget _buildCreditsTab() {
    return ListView.builder(
      padding: const EdgeInsets.only(
        left: 16,
        right: 16,
        top: 12,
        bottom: 88, // Extra padding for FAB
      ),
      itemCount: MockData.leaveCredits.length,
      itemBuilder: (context, index) {
        final credit = MockData.leaveCredits[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey.shade200, width: 1),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  credit.leaveType,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: LinearProgressIndicator(
                    value: credit.used / credit.earned,
                    minHeight: 8,
                    backgroundColor: Colors.grey.shade200,
                    valueColor: AlwaysStoppedAnimation(
                      credit.available > 0
                          ? Colors.green.shade400
                          : Colors.red.shade400,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Available',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        Text(
                          '${credit.available.toStringAsFixed(1)} days',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Used',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        Text(
                          '${credit.used.toStringAsFixed(1)} days',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Total',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        Text(
                          '${credit.earned.toStringAsFixed(1)} days',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _viewLeaveDetails(BuildContext context, dynamic request) {
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
                    'Leave Request Details',
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
              _buildDetailRow('Leave Type', request.leaveType),
              _buildDetailRow(
                'Start Date',
                request.startDate.toString().split(' ')[0],
              ),
              _buildDetailRow(
                'End Date',
                request.endDate.toString().split(' ')[0],
              ),
              _buildDetailRow(
                'Number of Days',
                '${request.numberOfDays} day${request.numberOfDays > 1 ? 's' : ''}',
              ),
              _buildDetailRow('Reason', request.reason),
              const Divider(height: 24),
              StatusBadge(
                label: request.status,
                status: request.status.toLowerCase(),
              ),
              if (request.approverName != null) ...[
                const SizedBox(height: 12),
                _buildDetailRow('Approved By', request.approverName ?? '-'),
                if (request.approvedDate != null)
                  _buildDetailRow(
                    'Approved Date',
                    request.approvedDate.toString().split(' ')[0],
                  ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF0F172A),
            ),
          ),
        ],
      ),
    );
  }

  void _showFileLeaveDialog(BuildContext context) {
    String leaveType = 'Vacation Leave';
    final startDateController = TextEditingController();
    final endDateController = TextEditingController();
    final reasonController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            8,
            20,
            MediaQuery.of(context).viewInsets.bottom + 24,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'File Leave Request',
                  style: GoogleFonts.inter(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  initialValue: leaveType,
                  decoration: _inputDecoration('Leave Type'),
                  items:
                      const [
                            'Vacation Leave',
                            'Sick Leave',
                            'Special Privilege Leave',
                            'Bereavement Leave',
                          ]
                          .map(
                            (type) => DropdownMenuItem(
                              value: type,
                              child: Text(type),
                            ),
                          )
                          .toList(),
                  onChanged: (value) {
                    if (value != null) {
                      setSheetState(() => leaveType = value);
                    }
                  },
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: startDateController,
                  decoration: _inputDecoration('Start Date'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: endDateController,
                  decoration: _inputDecoration('End Date'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: reasonController,
                  minLines: 3,
                  maxLines: 4,
                  decoration: _inputDecoration('Reason'),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () {},
                  icon: const Icon(Icons.attach_file),
                  label: const Text('Attach Supporting Document'),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Submit Leave Request'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String label) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    );
  }
}
