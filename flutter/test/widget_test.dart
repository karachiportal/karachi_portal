import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:karachi_portal/main.dart';

void main() {
  testWidgets('App renders login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const KarachiPortalApp());
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
