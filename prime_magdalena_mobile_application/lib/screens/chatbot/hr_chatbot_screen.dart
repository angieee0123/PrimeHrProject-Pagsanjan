import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:prime_magdalena_mobile_application/services/chatbot_service.dart';

class HrChatbotScreen extends StatefulWidget {
  const HrChatbotScreen({super.key});

  @override
  State<HrChatbotScreen> createState() => _HrChatbotScreenState();
}

class _HrChatbotScreenState extends State<HrChatbotScreen> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();
  final _chatbotService = ChatbotService();

  final List<_ChatMessage> _messages = [
    _ChatMessage(
      text:
          "Hello! I'm your PRIME HRIS assistant. I can help with your leave, payslip, attendance, training, and travel records, and explain how the system works. I only access your own account data—not other employees.",
      isBot: true,
    ),
  ];

  bool _isSending = false;
  List<String> _followUps = const [
    'What is my leave balance?',
    'How do I file a leave request?',
    'Show my latest payslip',
    'How is late deduction calculated?',
  ];

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scrollController.hasClients) return;
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOut,
      );
    });
  }

  Future<void> _sendMessage([String? text]) async {
    final content = (text ?? _messageController.text).trim();
    if (content.isEmpty || _isSending) return;

    setState(() {
      _isSending = true;
      _messages.add(_ChatMessage(text: content, isBot: false));
      _messageController.clear();
      _followUps = [];
    });
    _scrollToBottom();

    try {
      final result = await _chatbotService.sendMessage(content);
      if (!mounted) return;
      setState(() {
        _messages.add(_ChatMessage(text: result.response, isBot: true));
        _followUps = result.followUpQuestions;
        _isSending = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _messages.add(
          _ChatMessage(
            text: e.toString().replaceAll('Exception: ', ''),
            isBot: true,
            isError: true,
          ),
        );
        _isSending = false;
        _followUps = const [
          'What is my leave balance?',
          'How do I file leave?',
        ];
      });
    }
    _scrollToBottom();
  }

  void _clearChat() {
    setState(() {
      _messages
        ..clear()
        ..add(
          const _ChatMessage(
            text:
                'Conversation cleared. Ask me about your leave, payslip, attendance, training, travel, or HR policies.',
            isBot: true,
          ),
        );
      _followUps = const [
        'What is my leave balance?',
        'How do I file a leave request?',
      ];
    });
  }

  String _quickPrompt(String topic) {
    switch (topic) {
      case 'Leave':
        return 'What is my leave balance?';
      case 'Payslip':
        return 'What is my latest payslip?';
      case 'Attendance':
        return 'How is my attendance this month?';
      case 'Training':
        return 'How many verified training hours do I have?';
      case 'Travel':
        return 'What are my travel orders?';
      default:
        return 'How can you help me?';
    }
  }

  @override
  Widget build(BuildContext context) {

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0B044D), Color(0xFF1E3A8A)],
                ),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.smart_toy_rounded,
                color: Colors.white,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'HR Assistant',
                  style: GoogleFonts.inter(fontWeight: FontWeight.w700),
                ),
                Text(
                  'Your data only',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.delete_outline),
            tooltip: 'Clear chat',
            onPressed: _clearChat,
          ),
        ],
      ),
      body: Column(
        children: [
          // Quick Topic Chips
          SizedBox(
            height: 52,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              children: ['Leave', 'Payslip', 'Attendance', 'Training', 'Travel']
                  .map(
                    (topic) => Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: ActionChip(
                        label: Text(topic),
                        onPressed: _isSending
                            ? null
                            : () => _sendMessage(_quickPrompt(topic)),
                            backgroundColor: Colors.white,
                            side: BorderSide(color: Colors.grey.shade300),
                            labelStyle: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      )
                      .toList(),
            ),
          ),
          if (_followUps.isNotEmpty)
            SizedBox(
              height: 40,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                children: _followUps
                    .map(
                      (q) => Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: ActionChip(
                          label: Text(
                            q,
                            style: GoogleFonts.inter(fontSize: 11),
                          ),
                          onPressed:
                              _isSending ? null : () => _sendMessage(q),
                          visualDensity: VisualDensity.compact,
                        ),
                      ),
                    )
                    .toList(),
              ),
            ),
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length + (_isSending ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == _messages.length && _isSending) {
                  return Align(
                    alignment: Alignment.centerLeft,
                    child: Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 14,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.grey.shade600,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Text(
                            'Thinking...',
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              color: Colors.grey.shade700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }
                final message = _messages[index];
                return Align(
                  alignment: message.isBot
                      ? Alignment.centerLeft
                      : Alignment.centerRight,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    constraints: BoxConstraints(
                      maxWidth: MediaQuery.of(context).size.width * 0.82,
                    ),
                    decoration: BoxDecoration(
                      color: message.isError
                          ? const Color(0xFFFEE2E2)
                          : message.isBot
                              ? Colors.grey.shade100
                              : const Color(0xFF1E3A8A),
                      borderRadius: BorderRadius.only(
                        topLeft: const Radius.circular(16),
                        topRight: const Radius.circular(16),
                        bottomLeft: Radius.circular(message.isBot ? 4 : 16),
                        bottomRight: Radius.circular(message.isBot ? 16 : 4),
                      ),
                    ),
                    child: Text(
                      message.text,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        height: 1.45,
                        color: message.isError
                            ? const Color(0xFF991B1B)
                            : message.isBot
                                ? Colors.black87
                                : Colors.white,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          // Input Area
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: SafeArea(
              top: false,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _messageController,
                        enabled: !_isSending,
                        decoration: InputDecoration(
                          hintText: 'Ask about your HR records...',
                          hintStyle: GoogleFonts.inter(
                            fontSize: 14,
                            color: Colors.grey.shade500,
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 12,
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(24),
                          ),
                          filled: true,
                          fillColor: Colors.grey.shade50,
                        ),
                        style: GoogleFonts.inter(fontSize: 14),
                        maxLines: 4,
                        minLines: 1,
                        textCapitalization: TextCapitalization.sentences,
                        onSubmitted: _isSending ? null : (_) => _sendMessage(),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: _isSending
                              ? [Colors.grey.shade400, Colors.grey.shade500]
                              : const [
                                  Color(0xFF0B044D),
                                  Color(0xFF1E3A8A),
                                ],
                        ),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: IconButton(
                        onPressed: _isSending ? null : () => _sendMessage(),
                        icon: _isSending
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(
                                Icons.send_rounded,
                                color: Colors.white,
                                size: 20,
                              ),
                        padding: EdgeInsets.zero,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ChatMessage {
  final String text;
  final bool isBot;
  final bool isError;

  const _ChatMessage({
    required this.text,
    required this.isBot,
    this.isError = false,
  });
}
