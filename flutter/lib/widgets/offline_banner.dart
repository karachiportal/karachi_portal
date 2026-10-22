import 'package:flutter/material.dart';

class OfflineBanner extends StatelessWidget {
  final bool isOffline;
  const OfflineBanner({super.key, required this.isOffline});

  @override
  Widget build(BuildContext context) {
    if (!isOffline) return const SizedBox.shrink();
    return Container(
      width: double.infinity,
      padding: EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      color: Colors.orange.shade100,
      child: Row(
        children: [
          Icon(Icons.cloud_off, size: 16, color: Colors.orange.shade800),
          SizedBox(width: 8),
          Expanded(
            child: Text(
              'You are offline — showing saved data\nآپ آف لائن ہیں — محفوظ ڈیٹا دکھایا جا رہا ہے',
              style: TextStyle(
                fontSize: 11.5,
                color: Colors.orange.shade900,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
