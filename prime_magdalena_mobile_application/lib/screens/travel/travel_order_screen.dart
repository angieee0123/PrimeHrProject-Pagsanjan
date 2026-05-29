import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/models/models.dart';
import 'package:prime_magdalena_mobile_application/utils/mock_data.dart';

class TravelOrderScreen extends StatefulWidget {
  const TravelOrderScreen({super.key});

  @override
  State<TravelOrderScreen> createState() => _TravelOrderScreenState();
}

class _TravelOrderScreenState extends State<TravelOrderScreen> {
  String _statusFilter = 'All';

  List<TravelOrder> get _orders {
    if (_statusFilter == 'All') return MockData.travelOrders;
    return MockData.travelOrders
        .where((order) => order.status == _statusFilter)
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    final pending = MockData.travelOrders
        .where((order) => order.status.toLowerCase() == 'pending')
        .length;
    final approved = MockData.travelOrders
        .where((order) => order.status.toLowerCase() == 'approved')
        .length;
    final rejected = MockData.travelOrders
        .where((order) => order.status.toLowerCase() == 'rejected')
        .length;

    return FloatingPageScaffold(
      topbarHeight: FloatingPageScaffold.compactTopbarHeight,
      topbar: const FloatingScreenTopbar(
        eyebrow: 'Official Travel',
        title: 'Travel Orders',
        subtitle: 'File & track travel requests',
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: const Color(0xFF1E3A8A),
        foregroundColor: Colors.white,
        onPressed: _showFileTravelOrderSheet,
        child: const Icon(Icons.add),
      ),
      body: ListView(
        padding: const EdgeInsets.only(bottom: 96),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: StatCard(
                        label: 'Total',
                        value: '${MockData.travelOrders.length}',
                        icon: Icons.flight_takeoff,
                        backgroundColor: const Color(0xFFEFF6FF),
                        iconColor: const Color(0xFF2563EB),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: StatCard(
                        label: 'Pending',
                        value: '$pending',
                        icon: Icons.schedule,
                        backgroundColor: const Color(0xFFFEF3C7),
                        iconColor: const Color(0xFFD97706),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: StatCard(
                        label: 'Approved',
                        value: '$approved',
                        icon: Icons.check_circle,
                        backgroundColor: const Color(0xFFDCFCE7),
                        iconColor: const Color(0xFF059669),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: StatCard(
                        label: 'Rejected',
                        value: '$rejected',
                        icon: Icons.cancel,
                        backgroundColor: const Color(0xFFFFE4E4),
                        iconColor: const Color(0xFFDC2626),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: DropdownButtonFormField<String>(
              initialValue: _statusFilter,
              decoration: _inputDecoration('Filter by status'),
              items: const ['All', 'Pending', 'Approved', 'Rejected']
                  .map(
                    (status) =>
                        DropdownMenuItem(value: status, child: Text(status)),
                  )
                  .toList(),
              onChanged: (value) => setState(() => _statusFilter = value!),
            ),
          ),
          const SizedBox(height: 8),
          ..._orders.map(
            (order) => RecordCard(
              title: order.destination,
              subtitle: order.purpose,
              details: [
                {'label': 'Travel Date', 'value': _date(order.travelDate)},
                {'label': 'Return Date', 'value': _date(order.returnDate)},
              ],
              badge: StatusBadgeData(
                label: order.status,
                status: order.status.toLowerCase(),
              ),
              actions: [
                ActionButton(
                  label: 'View',
                  icon: Icons.visibility,
                  onTap: () => _showTravelDetails(order),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _showTravelDetails(TravelOrder order) {
    showModalBottomSheet(
      context: context,
      showDragHandle: true,
      builder: (_) => Padding(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(child: _sheetTitle(order.destination)),
                StatusBadge(label: order.status, status: order.status),
              ],
            ),
            const SizedBox(height: 16),
            _detailRow('Purpose', order.purpose),
            _detailRow('Travel Date', _date(order.travelDate)),
            _detailRow('Return Date', _date(order.returnDate)),
            _detailRow('Remarks', order.remarks ?? 'No remarks yet'),
            if (order.status.toLowerCase() == 'pending') ...[
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.close),
                  label: const Text('Cancel Travel Order'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  void _showFileTravelOrderSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (_) => Padding(
        padding: EdgeInsets.fromLTRB(
          20,
          8,
          20,
          MediaQuery.of(context).viewInsets.bottom + 24,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Align(
              alignment: Alignment.centerLeft,
              child: _sheetTitle('File Travel Order'),
            ),
            const SizedBox(height: 16),
            TextField(decoration: _inputDecoration('Destination')),
            const SizedBox(height: 12),
            TextField(decoration: _inputDecoration('Purpose')),
            const SizedBox(height: 12),
            TextField(decoration: _inputDecoration('Travel Date')),
            const SizedBox(height: 12),
            TextField(decoration: _inputDecoration('Return Date')),
            const SizedBox(height: 12),
            TextField(
              minLines: 3,
              maxLines: 4,
              decoration: _inputDecoration('Remarks'),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Submit Travel Order'),
              ),
            ),
          ],
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

  Widget _sheetTitle(String title) {
    return Text(
      title,
      style: GoogleFonts.inter(fontSize: 18, fontWeight: FontWeight.w700),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 96,
            child: Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 12,
                color: Colors.grey.shade600,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: GoogleFonts.inter(
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _date(DateTime? date) {
    if (date == null) return '-';
    return '${date.month}/${date.day}/${date.year}';
  }
}
