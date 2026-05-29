import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Compact gradient header for secondary tab screens (payslip, attendance, etc.).
class FloatingScreenTopbar extends StatelessWidget {
  final String title;
  final String? subtitle;
  final String? eyebrow;
  final List<Widget>? actions;
  final bool floating;

  const FloatingScreenTopbar({
    required this.title,
    this.subtitle,
    this.eyebrow,
    this.actions,
    this.floating = true,
    super.key,
  });

  static const _gradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFF0B044D), Color(0xFF1E3A8A)],
  );

  @override
  Widget build(BuildContext context) {
    final topPadding = floating ? 12.0 : MediaQuery.paddingOf(context).top + 12;

    return Container(
      decoration: const BoxDecoration(gradient: _gradient),
      padding: EdgeInsets.fromLTRB(16, topPadding, 12, 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                if (eyebrow != null && eyebrow!.isNotEmpty) ...[
                  Text(
                    eyebrow!,
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: Colors.white.withValues(alpha: 0.75),
                      letterSpacing: 0.6,
                    ),
                  ),
                  const SizedBox(height: 2),
                ],
                Text(
                  title,
                  style: GoogleFonts.poppins(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                    height: 1.2,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                if (subtitle != null && subtitle!.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    subtitle!,
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w400,
                      color: Colors.white.withValues(alpha: 0.8),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
          if (actions != null) ...actions!,
        ],
      ),
    );
  }
}

/// White icon button styled for gradient topbars.
class FloatingTopbarIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  final String? tooltip;

  const FloatingTopbarIconButton({
    required this.icon,
    this.onPressed,
    this.tooltip,
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    final button = IconButton(
      onPressed: onPressed,
      tooltip: tooltip,
      icon: Icon(icon, color: Colors.white, size: 22),
      style: IconButton.styleFrom(
        backgroundColor: Colors.white.withValues(alpha: 0.12),
        minimumSize: const Size(40, 40),
      ),
    );
    return button;
  }
}
