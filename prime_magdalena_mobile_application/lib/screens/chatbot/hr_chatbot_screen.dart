import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class HrChatbotScreen extends StatelessWidget {
  const HrChatbotScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final messages = [
      _ChatMessage(
        text:
            'Hello! I can help with leave, payslip, attendance, training, and performance questions.',
        isBot: true,
      ),
      _ChatMessage(text: 'What is my leave balance?', isBot: false),
      _ChatMessage(
        text:
            'You currently have 8 vacation leave days and 7 sick leave days available.',
        isBot: true,
      ),
    ];

    return Scaffold(
      appBar: AppBar(title: const Text('HR Assistant')),
      body: Column(
        children: [
          SizedBox(
            height: 52,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              children:
                  ['Leave', 'Payslip', 'Attendance', 'Training', 'Performance']
                      .map(
                        (topic) => Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ActionChip(
                            label: Text(topic),
                            onPressed: () {},
                          ),
                        ),
                      )
                      .toList(),
            ),
          ),
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: messages.length,
              itemBuilder: (context, index) {
                final message = messages[index];
                return Align(
                  alignment: message.isBot
                      ? Alignment.centerLeft
                      : Alignment.centerRight,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(12),
                    constraints: BoxConstraints(
                      maxWidth: MediaQuery.of(context).size.width * 0.78,
                    ),
                    decoration: BoxDecoration(
                      color: message.isBot
                          ? Colors.grey.shade100
                          : const Color(0xFF1E3A8A),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Text(
                      message.text,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: message.isBot ? Colors.black87 : Colors.white,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: TextField(
                decoration: InputDecoration(
                  hintText: 'Ask HR Assistant...',
                  suffixIcon: IconButton(
                    icon: const Icon(Icons.send),
                    onPressed: () {},
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                  ),
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

  _ChatMessage({required this.text, required this.isBot});
}
