import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/models/travel_models.dart';
import 'package:prime_magdalena_mobile_application/services/travel_service.dart';
import 'package:url_launcher/url_launcher.dart';

class TravelOrderScreen extends StatefulWidget {
  const TravelOrderScreen({super.key});

  @override
  State<TravelOrderScreen> createState() => _TravelOrderScreenState();
}

class _TravelOrderScreenState extends State<TravelOrderScreen> {
  final _travelService = TravelService();

  TravelIndexData? _data;
  bool _isLoading = true;
  String? _errorMessage;
  String? _statusFilter;

  List<TravelOrderModel> get _filteredOrders {
    final orders = _data?.orders ?? [];
    if (_statusFilter == null) return orders;

    return orders.where((order) {
      final status = order.status.toLowerCase();
      switch (_statusFilter) {
        case 'pending':
          return status == 'pending';
        case 'approved':
          return status == 'approved';
        case 'rejected':
          return status == 'disapproved' || status == 'rejected';
        case 'cancelled':
          return status == 'cancelled';
        default:
          return true;
      }
    }).toList();
  }

  @override
  void initState() {
    super.initState();
    _loadTravelOrders();
  }

  Future<void> _loadTravelOrders() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await _travelService.getTravelOrders();
      if (!mounted) return;
      setState(() {
        _data = data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return FloatingPageScaffold(
      topbarHeight: FloatingPageScaffold.compactTopbarHeight,
      topbar: FloatingScreenTopbar(
        eyebrow: 'Official Travel',
        title: 'Travel Orders',
        subtitle: _data != null
            ? '${_data!.stats.total} total · ${_data!.stats.pending} pending'
            : 'File & track travel requests',
        actions: [
          FloatingTopbarIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Refresh',
            onPressed: _isLoading ? null : _loadTravelOrders,
          ),
        ],
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: FloatingActionButton.extended(
          onPressed: _data == null ? null : _showFileTravelOrderSheet,
          backgroundColor: const Color(0xFF1E3A8A),
          icon: const Icon(Icons.add_rounded, size: 22),
          label: Text(
            'File Travel',
            style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w600),
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null || _data == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.error_outline, size: 48, color: Colors.grey.shade500),
              const SizedBox(height: 16),
              Text(
                _errorMessage ?? 'Failed to load travel orders',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(color: Colors.grey.shade700),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadTravelOrders,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final stats = _data!.stats;
    final orders = _filteredOrders;

    return ListView(
      padding: const EdgeInsets.only(bottom: 96),
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
          child: Column(
            children: [
              Row(
                children: [
                  Expanded(
                    child: EnhancedStatCard(
                      label: 'Total Travel Orders',
                      value: '${stats.total}',
                      icon: Icons.place_outlined,
                      iconWrapColor: const Color(0xFFEEF2FF),
                      iconColor: const Color(0xFF0B044D),
                      dotColor: const Color(0xFF0B044D),
                      subtitle: 'All time',
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: EnhancedStatCard(
                      label: 'Pending Approval',
                      value: '${stats.pending}',
                      icon: Icons.schedule_rounded,
                      iconWrapColor: const Color(0xFFFEF3C7),
                      iconColor: const Color(0xFFA16207),
                      dotColor: const Color(0xFFA16207),
                      subtitle: 'Awaiting approval',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: EnhancedStatCard(
                      label: 'Approved',
                      value: '${stats.approved}',
                      icon: Icons.check_circle_outline_rounded,
                      iconWrapColor: const Color(0xFFDCFCE7),
                      iconColor: const Color(0xFF15803D),
                      dotColor: const Color(0xFF15803D),
                      subtitle: 'Successfully approved',
                      isCompact: true,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: EnhancedStatCard(
                      label: 'Rejected',
                      value: '${stats.rejected}',
                      icon: Icons.cancel_outlined,
                      iconWrapColor: const Color(0xFFFFE4E4),
                      iconColor: const Color(0xFF8E1E18),
                      dotColor: const Color(0xFF8E1E18),
                      subtitle: 'Rejected requests',
                      isCompact: true,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: DropdownButtonFormField<String?>(
            initialValue: _statusFilter,
            decoration: _inputDecoration('Filter by status'),
            items: const [
              DropdownMenuItem<String?>(value: null, child: Text('All Status')),
              DropdownMenuItem(value: 'pending', child: Text('Pending')),
              DropdownMenuItem(value: 'approved', child: Text('Approved')),
              DropdownMenuItem(value: 'rejected', child: Text('Rejected')),
              DropdownMenuItem(value: 'cancelled', child: Text('Cancelled')),
            ],
            onChanged: (value) => setState(() => _statusFilter = value),
          ),
        ),
        const SizedBox(height: 8),
        if (orders.isEmpty)
          Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              children: [
                Icon(Icons.map_outlined, size: 48, color: Colors.grey.shade400),
                const SizedBox(height: 12),
                Text(
                  'No travel orders found',
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'File your first travel order using the button below',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          )
        else
          ...orders.map(
            (order) => RecordCard(
              title: order.destination,
              subtitle: order.purpose,
              details: [
                if (order.orderNumber != null)
                  {'label': 'Order No.', 'value': order.orderNumber!},
                {
                  'label': 'Travel Date',
                  'value': _formatDate(order.travelDate),
                },
                {
                  'label': 'Return Date',
                  'value': _formatDate(order.returnDate),
                },
                {
                  'label': 'Duration',
                  'value':
                      '${order.duration} day${order.duration == 1 ? '' : 's'}',
                },
              ],
              badge: StatusBadgeData(
                label: order.statusLabel,
                status: order.badgeStatus,
              ),
              actions: [
                if (order.isPending)
                  ActionButton(
                    label: 'Cancel',
                    icon: Icons.close,
                    onTap: () => _confirmCancel(order),
                  ),
                ActionButton(
                  label: 'View',
                  icon: Icons.visibility_outlined,
                  onTap: () => _showTravelDetails(order),
                ),
              ],
            ),
          ),
      ],
    );
  }

  Future<void> _confirmCancel(TravelOrderModel order) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel travel order?'),
        content: Text(
          'Cancel travel to ${order.destination}? This cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Yes, cancel'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    try {
      final message = await _travelService.cancelTravelOrder(order.id);
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(message)));
      await _loadTravelOrders();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
      );
    }
  }

  Future<void> _showTravelDetails(TravelOrderModel order) async {
    TravelOrderModel detail = order;
    try {
      detail = await _travelService.getTravelOrder(order.id);
    } catch (_) {
      // Use list data if detail fetch fails.
    }

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (sheetContext) => Padding(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'TRAVEL ORDER · ${detail.orderNumber ?? detail.id}',
                style: GoogleFonts.inter(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: Text(
                      detail.destination,
                      style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  StatusBadge(
                    label: detail.statusLabel,
                    status: detail.badgeStatus,
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _detailRow('Purpose', detail.purpose),
              _detailRow('Departure', _formatDate(detail.travelDate)),
              _detailRow('Return', _formatDate(detail.returnDate)),
              _detailRow('Duration', '${detail.duration} days'),
              _detailRow(
                'Transportation',
                detail.transportationMode ?? 'Not specified',
              ),
              _detailRow(
                'Estimated Budget',
                detail.estimatedBudget != null
                    ? '₱${NumberFormat('#,##0.00').format(detail.estimatedBudget)}'
                    : 'Not specified',
              ),
              if (detail.processedBy != null) ...[
                const Divider(height: 24),
                Text(
                  'APPROVAL INFORMATION',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Colors.grey.shade600,
                  ),
                ),
                const SizedBox(height: 8),
                _detailRow('Processed By', detail.processedBy!),
                if (detail.processedAt != null)
                  _detailRow(
                    'Date Processed',
                    _formatDate(detail.processedAt!),
                  ),
              ],
              if (detail.disapprovalReason != null &&
                  detail.disapprovalReason!.isNotEmpty) ...[
                const SizedBox(height: 8),
                _detailRow('Remarks', detail.disapprovalReason!),
              ],
              if (detail.attachmentUrl != null) ...[
                const SizedBox(height: 16),
                OutlinedButton.icon(
                  onPressed: () async {
                    final uri = Uri.parse(detail.attachmentUrl!);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(
                        uri,
                        mode: LaunchMode.externalApplication,
                      );
                    }
                  },
                  icon: const Icon(Icons.attach_file),
                  label: const Text('View Attachment'),
                ),
              ],
              if (detail.isPending) ...[
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.pop(sheetContext);
                      _confirmCancel(detail);
                    },
                    icon: const Icon(Icons.cancel_outlined),
                    label: const Text('Cancel Travel Order'),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _showFileTravelOrderSheet() async {
    final destinationController = TextEditingController();
    final purposeController = TextEditingController();
    final budgetController = TextEditingController();
    DateTime? travelDate;
    DateTime? returnDate;
    String? transportationMode;
    dynamic? attachment;
    int duration = 1;
    String? formError;
    bool submitting = false;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (sheetContext) => StatefulBuilder(
        builder: (context, setSheetState) {
          void recalcDuration() {
            if (travelDate != null && returnDate != null) {
              duration = TravelService.calculateDuration(
                travelDate!,
                returnDate!,
              );
              if (returnDate!.isBefore(travelDate!)) {
                formError = 'Return date must be on or after departure date';
              } else if (duration < 1) {
                formError = 'Please select valid travel dates';
              } else {
                formError = null;
              }
            }
          }

          Future<void> pickDate({required bool isDeparture}) async {
            final picked = await showDatePicker(
              context: context,
              initialDate: isDeparture
                  ? (travelDate ?? DateTime.now())
                  : (returnDate ?? travelDate ?? DateTime.now()),
              firstDate: DateTime.now().subtract(const Duration(days: 1)),
              lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
            );
            if (picked == null) return;
            setSheetState(() {
              if (isDeparture) {
                travelDate = picked;
              } else {
                returnDate = picked;
              }
              recalcDuration();
            });
          }

          /*
          Future<void> pickFile() async {
            final result = await FilePicker.platform.pickFiles(
              type: FileType.custom,
              allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
            );
            if (result != null && result.files.isNotEmpty) {
              final file = result.files.first;
              if (file.size > 5 * 1024 * 1024) {
                setSheetState(() => formError = 'File size exceeds 5MB limit');
                return;
              }
              setSheetState(() {
                attachment = file;
                formError = null;
              });
            }
          }
          */

          Future<void> submit() async {
            if (destinationController.text.trim().isEmpty ||
                purposeController.text.trim().isEmpty ||
                travelDate == null ||
                returnDate == null) {
              setSheetState(
                () => formError = 'Please complete all required fields',
              );
              return;
            }
            if (purposeController.text.trim().length > 300) {
              setSheetState(
                () => formError = 'Purpose must be 300 characters or less',
              );
              return;
            }
            if (duration < 1) {
              setSheetState(
                () => formError = 'Please select valid travel dates',
              );
              return;
            }

            setSheetState(() => submitting = true);
            try {
              final message = await _travelService.submitTravelOrder(
                destination: destinationController.text.trim(),
                purpose: purposeController.text.trim(),
                travelDate: DateFormat('yyyy-MM-dd').format(travelDate!),
                returnDate: DateFormat('yyyy-MM-dd').format(returnDate!),
                duration: duration,
                transportationMode: transportationMode,
                estimatedBudget: double.tryParse(budgetController.text.trim()),
                attachment: attachment,
              );
              if (!context.mounted) return;
              Navigator.pop(sheetContext);
              if (!mounted) return;
              ScaffoldMessenger.of(
                this.context,
              ).showSnackBar(SnackBar(content: Text(message)));
              await _loadTravelOrders();
            } catch (e) {
              setSheetState(() {
                submitting = false;
                formError = e.toString().replaceAll('Exception: ', '');
              });
            }
          }

          return Padding(
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
                    'File Travel Order',
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: destinationController,
                    decoration: _inputDecoration('Destination'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: purposeController,
                    minLines: 3,
                    maxLines: 4,
                    maxLength: 300,
                    decoration: _inputDecoration('Purpose of Travel'),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => pickDate(isDeparture: true),
                          child: Text(
                            travelDate == null
                                ? 'Departure Date'
                                : DateFormat('yyyy-MM-dd').format(travelDate!),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => pickDate(isDeparture: false),
                          child: Text(
                            returnDate == null
                                ? 'Return Date'
                                : DateFormat('yyyy-MM-dd').format(returnDate!),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    readOnly: true,
                    decoration: _inputDecoration('Total Duration (days)'),
                    controller: TextEditingController(text: '$duration'),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String?>(
                    initialValue: transportationMode,
                    decoration: _inputDecoration('Mode of Transportation'),
                    items: const [
                      DropdownMenuItem<String?>(
                        value: null,
                        child: Text('Select mode...'),
                      ),
                      DropdownMenuItem(
                        value: 'Private Vehicle',
                        child: Text('Private Vehicle'),
                      ),
                      DropdownMenuItem(
                        value: 'Government Vehicle',
                        child: Text('Government Vehicle'),
                      ),
                      DropdownMenuItem(
                        value: 'Public Transportation',
                        child: Text('Public Transportation'),
                      ),
                      DropdownMenuItem(
                        value: 'Air Travel',
                        child: Text('Air Travel'),
                      ),
                      DropdownMenuItem(value: 'Other', child: Text('Other')),
                    ],
                    onChanged: (v) =>
                        setSheetState(() => transportationMode = v),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: budgetController,
                    keyboardType: const TextInputType.numberWithOptions(
                      decimal: true,
                    ),
                    decoration: _inputDecoration('Estimated Budget (optional)'),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: null,
                    icon: const Icon(Icons.attach_file),
                    label: Text(
                      attachment?.name ??
                          'Attach Supporting Document (optional)',
                    ),
                  ),
                  if (formError != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      formError!,
                      style: GoogleFonts.inter(
                        color: Colors.red.shade700,
                        fontSize: 12,
                      ),
                    ),
                  ],
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: submitting ? null : submit,
                      child: submitting
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Submit Travel Order'),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );

    destinationController.dispose();
    purposeController.dispose();
    budgetController.dispose();
  }

  InputDecoration _inputDecoration(String label) {
    return InputDecoration(
      labelText: label,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
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

  String _formatDate(String iso) {
    final parsed = DateTime.tryParse(iso);
    if (parsed == null) return iso;
    return DateFormat('MMM d, y').format(parsed);
  }
}
