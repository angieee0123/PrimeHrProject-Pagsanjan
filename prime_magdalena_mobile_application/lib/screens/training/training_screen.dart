import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:prime_magdalena_mobile_application/components/index.dart';
import 'package:prime_magdalena_mobile_application/models/training_models.dart';
import 'package:prime_magdalena_mobile_application/services/training_service.dart';
import 'package:url_launcher/url_launcher.dart';

class TrainingScreen extends StatefulWidget {
  const TrainingScreen({super.key});

  @override
  State<TrainingScreen> createState() => _TrainingScreenState();
}

class _TrainingScreenState extends State<TrainingScreen> {
  final _trainingService = TrainingService();
  final _searchController = TextEditingController();

  TrainingIndexData? _data;
  bool _isLoading = true;
  String? _errorMessage;
  String? _statusFilter;
  String? _positionFilter;

  @override
  void initState() {
    super.initState();
    _searchController.addListener(() => setState(() {}));
    _loadTraining();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadTraining() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final data = await _trainingService.getTrainingData();
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

  List<TrainingRecordModel> get _filteredTrainings {
    final query = _searchController.text.trim().toLowerCase();
    return (_data?.trainings ?? []).where((t) {
      final statusOk = _statusFilter == null || t.status == _statusFilter;
      final positionOk =
          _positionFilter == null || t.positionType == _positionFilter;
      final searchOk = query.isEmpty ||
          t.title.toLowerCase().contains(query) ||
          t.status.toLowerCase().contains(query) ||
          t.conductedBy.toLowerCase().contains(query) ||
          t.refDocNo.toLowerCase().contains(query);
      return statusOk && positionOk && searchOk;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return FloatingPageScaffold(
      topbarHeight: FloatingPageScaffold.compactTopbarHeight,
      topbar: FloatingScreenTopbar(
        eyebrow: 'Learning & Development',
        title: 'Training',
        subtitle: _data != null
            ? 'FY ${_data!.fiscalYear} · ${_data!.stats.totalHours} verified hrs'
            : 'L&D hours & training history',
        actions: [
          FloatingTopbarIconButton(
            icon: Icons.refresh_rounded,
            tooltip: 'Refresh',
            onPressed: _isLoading ? null : _loadTraining,
          ),
        ],
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: FloatingActionButton.extended(
          onPressed: _data == null ? null : _showAddTrainingSheet,
          backgroundColor: const Color(0xFF1E3A8A),
          icon: const Icon(Icons.add_rounded, size: 22),
          label: Text(
            'Add Training',
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
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
                _errorMessage ?? 'Failed to load training data',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(color: Colors.grey.shade700),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadTraining,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final stats = _data!.stats;
    final breakdown = _data!.breakdown;
    final trainings = _filteredTrainings;
    final goalHours = _data!.goalHours;

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
                      label: 'Total L&D Hours',
                      value: '${stats.totalHours}',
                      icon: Icons.schedule_rounded,
                      iconWrapColor: const Color(0xFFEEF2FF),
                      iconColor: const Color(0xFF0B044D),
                      dotColor: const Color(0xFF0B044D),
                      subtitle:
                          '${stats.totalHours} of $goalHours hrs · FY ${_data!.fiscalYear}',
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: EnhancedStatCard(
                      label: 'Verified',
                      value: '${stats.verified}',
                      icon: Icons.verified_outlined,
                      iconWrapColor: const Color(0xFFDCFCE7),
                      iconColor: const Color(0xFF15803D),
                      dotColor: const Color(0xFF15803D),
                      subtitle: 'Hours credited to PDS',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: EnhancedStatCard(
                      label: 'Pending',
                      value: '${stats.pending}',
                      icon: Icons.hourglass_empty_rounded,
                      iconWrapColor: const Color(0xFFFEF3C7),
                      iconColor: const Color(0xFFA16207),
                      dotColor: const Color(0xFFA16207),
                      subtitle: 'Awaiting HR verification',
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
                      subtitle: 'Needs correction',
                      isCompact: true,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: LinearProgressIndicator(
                  value: (stats.goalProgress / 100).clamp(0.0, 1.0),
                  minHeight: 8,
                  backgroundColor: Colors.grey.shade200,
                  valueColor: const AlwaysStoppedAnimation(Color(0xFF1E3A8A)),
                ),
              ),
              const SizedBox(height: 4),
              Align(
                alignment: Alignment.centerRight,
                child: Text(
                  '${stats.goalProgress}% of annual goal',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: Colors.grey.shade600,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _buildBreakdownPanel(breakdown),
        ),
        const SizedBox(height: 16),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: 'Search title, status, or sponsor...',
              prefixIcon: const Icon(Icons.search),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              isDense: true,
            ),
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _statusChip('All', null),
                _statusChip('Verified', 'verified'),
                _statusChip('Pending', 'pending'),
                _statusChip('Rejected', 'rejected'),
              ],
            ),
          ),
        ),
        const SizedBox(height: 8),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: DropdownButtonFormField<String?>(
            initialValue: _positionFilter,
            decoration: InputDecoration(
              labelText: 'Position type',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              isDense: true,
            ),
            items: const [
              DropdownMenuItem<String?>(
                value: null,
                child: Text('All position types'),
              ),
              DropdownMenuItem(value: 'Managerial', child: Text('Managerial')),
              DropdownMenuItem(
                value: 'Supervisory',
                child: Text('Supervisory'),
              ),
              DropdownMenuItem(value: 'Technical', child: Text('Technical')),
              DropdownMenuItem(value: 'Clerical', child: Text('Clerical')),
            ],
            onChanged: (v) => setState(() => _positionFilter = v),
          ),
        ),
        const SizedBox(height: 8),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Text(
            'Showing ${trainings.length} training record(s)',
            style: GoogleFonts.inter(
              fontSize: 12,
              color: Colors.grey.shade600,
            ),
          ),
        ),
        const SizedBox(height: 8),
        if (trainings.isEmpty)
          Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              children: [
                Icon(Icons.school_outlined,
                    size: 48, color: Colors.grey.shade400),
                const SizedBox(height: 12),
                Text(
                  'No training records found',
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Tap Add Training to submit your first L&D record',
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
          ...trainings.map((t) => RecordCard(
                title: t.title,
                subtitle: t.conductedBy,
                details: [
                  {
                    'label': 'Dates',
                    'value':
                        '${_formatDate(t.dateFrom)} – ${_formatDate(t.dateTo)}',
                  },
                  {
                    'label': 'Hours',
                    'value':
                        '${t.hours} hr${t.hours == 1 ? '' : 's'} · ${t.positionType}',
                  },
                  {'label': 'Ref Doc', 'value': t.refDocNo},
                  {'label': 'L&D Category', 'value': t.ldCategoryLabel},
                ],
                badge: StatusBadgeData(
                  label: t.statusLabel,
                  status: t.badgeStatus,
                ),
                actions: [
                  if (t.isPending)
                    ActionButton(
                      label: 'Delete',
                      icon: Icons.delete_outline,
                      onTap: () => _confirmDelete(t),
                    ),
                  ActionButton(
                    label: 'View',
                    icon: Icons.visibility_outlined,
                    onTap: () => _showTrainingDetails(t),
                  ),
                ],
              )),
      ],
    );
  }

  Widget _statusChip(String label, String? value) {
    final selected = _statusFilter == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => setState(() => _statusFilter = value),
        selectedColor: const Color(0xFFEEF2FF),
        checkmarkColor: const Color(0xFF1E3A8A),
      ),
    );
  }

  Widget _buildBreakdownPanel(TrainingBreakdown breakdown) {
    final maxH = breakdown.maxHours == 0 ? 1 : breakdown.maxHours;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Breakdown by L&D Category',
            style: GoogleFonts.inter(
              fontWeight: FontWeight.w700,
              fontSize: 14,
            ),
          ),
          Text(
            'Verified L&D hours only · FY ${_data!.fiscalYear}',
            style: GoogleFonts.inter(
              fontSize: 11,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 12),
          _breakdownRow(
            'Leadership',
            breakdown.leadership,
            maxH,
            const Color(0xFF7C3AED),
          ),
          const SizedBox(height: 10),
          _breakdownRow(
            'Technical',
            breakdown.technical,
            maxH,
            const Color(0xFF2563EB),
          ),
          const SizedBox(height: 10),
          _breakdownRow(
            'Core / Foundation',
            breakdown.core,
            maxH,
            const Color(0xFF059669),
          ),
        ],
      ),
    );
  }

  Widget _breakdownRow(
    String label,
    int hours,
    int maxHours,
    Color color,
  ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: GoogleFonts.inter(fontSize: 12)),
            Text(
              '$hours hrs',
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: (hours / maxHours).clamp(0.0, 1.0),
            minHeight: 6,
            backgroundColor: Colors.grey.shade200,
            valueColor: AlwaysStoppedAnimation(color),
          ),
        ),
      ],
    );
  }

  Future<void> _confirmDelete(TrainingRecordModel record) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete training record?'),
        content: Text(
          'Delete "${record.title}"? This cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    try {
      final message = await _trainingService.deleteTraining(record.id);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
      await _loadTraining();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
      );
    }
  }

  Future<void> _showTrainingDetails(TrainingRecordModel record) async {
    TrainingRecordModel detail = record;
    try {
      detail = await _trainingService.getTraining(record.id);
    } catch (_) {}

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
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      detail.title,
                      style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  Row(
                    children: [
                      StatusBadge(
                        label: detail.statusLabel,
                        status: detail.badgeStatus,
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(sheetContext),
                        tooltip: 'Close',
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _detailRow('Conducted By', detail.conductedBy),
              _detailRow(
                'Inclusive Dates',
                '${_formatDate(detail.dateFrom)} – ${_formatDate(detail.dateTo)}',
              ),
              _detailRow('Hours', '${detail.hours}'),
              _detailRow('Position Type', detail.positionType),
              _detailRow('L&D Category', detail.ldCategoryLabel),
              _detailRow('Reference Doc', detail.refDocNo),
              if (detail.venue != null && detail.venue!.isNotEmpty)
                _detailRow('Venue', detail.venue!),
              if (detail.certNo != null && detail.certNo!.isNotEmpty)
                _detailRow('Certificate No.', detail.certNo!),
              if (detail.rejectedReason != null &&
                  detail.rejectedReason!.isNotEmpty) ...[
                const Divider(height: 24),
                _detailRow('Rejection Reason', detail.rejectedReason!),
              ],
              if (detail.certificateUrl != null) ...[
                const SizedBox(height: 16),
                OutlinedButton.icon(
                  onPressed: () async {
                    final uri = Uri.parse(detail.certificateUrl!);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(uri, mode: LaunchMode.externalApplication);
                    }
                  },
                  icon: const Icon(Icons.picture_as_pdf_outlined),
                  label: const Text('View Certificate'),
                ),
              ],
              if (detail.isPending) ...[
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.pop(sheetContext);
                      _confirmDelete(detail);
                    },
                    icon: const Icon(Icons.delete_outline),
                    label: const Text('Delete Record'),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _showAddTrainingSheet() async {
    PlatformFile? certificate;
    int step = 1;
    String? formError;
    bool submitting = false;

    final titleController = TextEditingController();
    final conductedByController = TextEditingController();
    final venueController = TextEditingController();
    final refDocController = TextEditingController();
    final certNoController = TextEditingController();
    final hoursController = TextEditingController();
    String? positionType;
    DateTime? dateFrom;
    DateTime? dateTo;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (sheetContext) => StatefulBuilder(
        builder: (context, setSheetState) {
          Future<void> pickCertificate() async {
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
                certificate = file;
                formError = null;
                step = 2;
              });
            }
          }

          Future<void> pickDate({required bool isFrom}) async {
            final picked = await showDatePicker(
              context: context,
              initialDate: isFrom
                  ? (dateFrom ?? DateTime.now())
                  : (dateTo ?? dateFrom ?? DateTime.now()),
              firstDate: DateTime(2000),
              lastDate: DateTime.now().add(const Duration(days: 365)),
            );
            if (picked == null) return;
            setSheetState(() {
              if (isFrom) {
                dateFrom = picked;
              } else {
                dateTo = picked;
              }
            });
          }

          Future<void> submit() async {
            if (certificate == null) {
              setSheetState(() => formError = 'Certificate is required');
              return;
            }
            final hours = int.tryParse(hoursController.text.trim());
            if (titleController.text.trim().isEmpty ||
                conductedByController.text.trim().isEmpty ||
                refDocController.text.trim().isEmpty ||
                positionType == null ||
                dateFrom == null ||
                dateTo == null ||
                hours == null ||
                hours < 1) {
              setSheetState(
                () => formError = 'Please complete all required fields',
              );
              return;
            }
            if (dateTo!.isBefore(dateFrom!)) {
              setSheetState(
                () => formError = 'End date must be on or after start date',
              );
              return;
            }

            setSheetState(() => submitting = true);
            try {
              final message = await _trainingService.submitTraining(
                title: titleController.text.trim(),
                conductedBy: conductedByController.text.trim(),
                dateFrom: DateFormat('yyyy-MM-dd').format(dateFrom!),
                dateTo: DateFormat('yyyy-MM-dd').format(dateTo!),
                hours: hours,
                positionType: positionType!,
                refDocNo: refDocController.text.trim(),
                venue: venueController.text.trim(),
                certNo: certNoController.text.trim(),
                certificate: certificate!,
              );
              if (!context.mounted) return;
              Navigator.pop(sheetContext);
              if (!mounted) return;
              ScaffoldMessenger.of(this.context)
                  .showSnackBar(SnackBar(content: Text(message)));
              await _loadTraining();
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
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Add New Training',
                            style: GoogleFonts.inter(
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Text(
                            step == 1
                                ? 'Step 1 of 2 — Upload your certificate'
                                : 'Step 2 of 2 — Review & submit',
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(sheetContext),
                        tooltip: 'Close',
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (step == 1) ...[
                    OutlinedButton.icon(
                      onPressed: pickCertificate,
                      icon: const Icon(Icons.upload_file),
                      label: Text(
                        certificate?.name ?? 'Upload Certificate (required)',
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Upload your Certificate of Completion (PDF or image, max 5MB). Fill in the training details on the next step.',
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    if (certificate != null) ...[
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () => setSheetState(() => step = 2),
                          child: const Text('Continue to Details'),
                        ),
                      ),
                    ],
                  ] else ...[
                    TextField(
                      controller: titleController,
                      decoration: _inputDecoration(
                        'Title of Seminar / Training',
                      ),
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: positionType,
                      decoration: _inputDecoration('Type of Position'),
                      items: const [
                        DropdownMenuItem(
                          value: 'Managerial',
                          child: Text('Managerial'),
                        ),
                        DropdownMenuItem(
                          value: 'Supervisory',
                          child: Text('Supervisory'),
                        ),
                        DropdownMenuItem(
                          value: 'Technical',
                          child: Text('Technical'),
                        ),
                        DropdownMenuItem(
                          value: 'Clerical',
                          child: Text('Clerical'),
                        ),
                      ],
                      onChanged: (v) => setSheetState(() => positionType = v),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: conductedByController,
                      decoration: _inputDecoration('Conducted / Sponsored By'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: venueController,
                      decoration: _inputDecoration('Venue (optional)'),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () => pickDate(isFrom: true),
                            child: Text(
                              dateFrom == null
                                  ? 'Date From'
                                  : DateFormat('yyyy-MM-dd')
                                      .format(dateFrom!),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () => pickDate(isFrom: false),
                            child: Text(
                              dateTo == null
                                  ? 'Date To'
                                  : DateFormat('yyyy-MM-dd').format(dateTo!),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: hoursController,
                      keyboardType: TextInputType.number,
                      decoration: _inputDecoration('Number of Hours'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: refDocController,
                      decoration: _inputDecoration('Reference Document No.'),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: certNoController,
                      decoration: _inputDecoration('Certificate No. (optional)'),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Certificate: ${certificate?.name ?? "—"}',
                      style: GoogleFonts.inter(fontSize: 12),
                    ),
                    TextButton(
                      onPressed: () => setSheetState(() => step = 1),
                      child: const Text('Change certificate'),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF3C7),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Submitted entries are marked Pending until HR verifies your certificate. Only verified hours count toward your annual L&D total.',
                        style: GoogleFonts.inter(fontSize: 11),
                      ),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: submitting ? null : submit,
                        child: submitting
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child:
                                    CircularProgressIndicator(strokeWidth: 2),
                              )
                            : const Text('Submit for Verification'),
                      ),
                    ),
                  ],
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
                ],
              ),
            ),
          );
        },
      ),
    );

    titleController.dispose();
    conductedByController.dispose();
    venueController.dispose();
    refDocController.dispose();
    certNoController.dispose();
    hoursController.dispose();
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
            width: 120,
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
