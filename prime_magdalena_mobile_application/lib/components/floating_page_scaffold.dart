import 'package:flutter/material.dart';

/// Shared layout: scrollable body with a pinned floating topbar (clears the drawer menu).
class FloatingPageScaffold extends StatelessWidget {
  /// Height of the topbar content area (excluding top safe-area offset).
  final double topbarHeight;
  final Widget topbar;
  final Widget body;
  final Color? backgroundColor;
  final Widget? floatingActionButton;
  final FloatingActionButtonLocation? floatingActionButtonLocation;

  const FloatingPageScaffold({
    required this.topbarHeight,
    required this.topbar,
    required this.body,
    this.backgroundColor,
    this.floatingActionButton,
    this.floatingActionButtonLocation,
    super.key,
  });

  /// Horizontal margin for the floating topbar.
  static const double horizontalMargin = 12;

  static const double topMargin = 8;

  static const double gapBelowTopbar = 10;

  /// Dashboard topbar approximate content height.
  static const double dashboardTopbarHeight = 158;

  /// Compact title topbar approximate content height.
  static const double compactTopbarHeight = 76;

  static double topOffset(BuildContext context) {
    return MediaQuery.paddingOf(context).top + topMargin;
  }

  static double contentTopInset(BuildContext context, double barHeight) {
    return topOffset(context) + barHeight + gapBelowTopbar;
  }

  @override
  Widget build(BuildContext context) {
    final contentTop = contentTopInset(context, topbarHeight);

    return Scaffold(
      backgroundColor: backgroundColor ?? const Color(0xFFF7F6FF),
      floatingActionButton: floatingActionButton,
      floatingActionButtonLocation: floatingActionButtonLocation,
      body: Stack(
        clipBehavior: Clip.none,
        children: [
          Positioned.fill(
            child: Padding(
              padding: EdgeInsets.only(top: contentTop),
              child: body,
            ),
          ),
          Positioned(
            left: horizontalMargin,
            right: horizontalMargin,
            top: topOffset(context),
            child: Material(
              elevation: 8,
              shadowColor: const Color(0xFF0B044D).withValues(alpha: 0.22),
              borderRadius: BorderRadius.circular(16),
              clipBehavior: Clip.antiAlias,
              child: topbar,
            ),
          ),
        ],
      ),
    );
  }
}
