import 'package:flutter/material.dart';
import 'package:intl/intl.dart' hide TextDirection;
import '../widgets/shared_widgets.dart';
import '../services/price_service.dart';

class PriceSearchPage extends StatefulWidget {
  final Map<String, dynamic>? initialItem;
  const PriceSearchPage({super.key, this.initialItem});

  @override
  State<PriceSearchPage> createState() => _PriceSearchPageState();
}

class _PriceSearchPageState extends State<PriceSearchPage> {
  final _searchCtrl = TextEditingController();
  Map<String, dynamic>? _result;
  List<Map<String, dynamic>> _trendData = [];
  Map<String, dynamic>? _priceDetails;
  bool _loading = false;
  bool _searched = false;
  String _trendPeriod = 'Last 7 Days';

  final List<String> _popularSearches = [
    'Rice',
    'Wheat Flour',
    'Sugar',
    'Cooking Oil',
    'Onion'
  ];

  @override
  void initState() {
    super.initState();
    if (widget.initialItem != null) {
      _result = widget.initialItem;
      _searchCtrl.text = widget.initialItem!['name'] ?? '';
      _searched = true;
      _loadTrend(widget.initialItem!['id']);
    }
  }

  Future<void> _search(String query) async {
    if (query.trim().isEmpty) return;
    setState(() => _loading = true);
    final res = await PriceService.searchItem(query);
    if (mounted) {
      setState(() {
        _loading = false;
        _searched = true;
        if (res['success'] == true && res['data'] != null) {
          _result = Map<String, dynamic>.from(res['data']);
          _loadTrend(_result!['id']);
        } else {
          _result = null;
          _trendData = [];
        }
      });
    }
  }

  Future<void> _loadTrend(dynamic itemId) async {
    final periodMap = {
      'Last 7 Days': '7days',
      'Last 30 Days': '30days',
      'Last 3 Months': '3months',
    };
    final res = await PriceService.getPriceTrend(
        itemId, periodMap[_trendPeriod] ?? '7days');
    if (mounted) {
      final currentPrice = (_result?['price'] ?? 142.0).toDouble();
      setState(() {
        if (res['success'] == true) {
          final trend = res['data']?['trend'];
          final hasTrend = trend != null && trend.isNotEmpty;
          _trendData = hasTrend
              ? List<Map<String, dynamic>>.from(trend)
              : _flatTrend(currentPrice);
          _priceDetails = hasTrend && res['data'] != null ? res['data']['stats'] : null;
        } else {
          _trendData = _flatTrend(currentPrice);
          _priceDetails = null;
        }
      });
    }
  }

  List<Map<String, dynamic>> _flatTrend(double price) {
    final now = DateTime.now();
    return [
      {'date': DateFormat('d MMM').format(now.subtract(Duration(days: 1))), 'price': price},
      {'date': DateFormat('d MMM').format(now), 'price': price},
    ];
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: const KarachiAppBar(showBack: true),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            SizedBox(height: 14),
            _buildSearchBar(),
            SizedBox(height: 14),
            _buildPopularSearches(),
            if (_loading)
              Padding(
                padding: EdgeInsets.all(40),
                child: Center(
                    child: CircularProgressIndicator(color: kPrimaryGreen)),
              ),
            if (!_loading && _searched && _result == null) _buildNoResult(),
            if (!_loading && _result != null) ...[
              SizedBox(height: 16),
              _buildResultCard(),
              SizedBox(height: 16),
              _buildTrendChart(),
              SizedBox(height: 16),
              _buildPriceDetails(),
              SizedBox(height: 16),
              _buildNoteCard(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(14),
        child: Row(
          children: [
            Container(
              padding: EdgeInsets.all(8),
              decoration: BoxDecoration(
                  color: kPrimaryGreen,
                  borderRadius: BorderRadius.circular(10)),
              child:
                  Icon(Icons.label_outline, color: Colors.white, size: 20),
            ),
            SizedBox(width: 10),
            Expanded(
              child: Text('Item Price Search   اشیاء کی قیمت تلاش کریں',
                  style:
                      TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Container(
                  padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                      color: kPrimaryGreen.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(8)),
                  child: Row(children: [
                    Icon(Icons.calendar_today,
                        size: 11, color: kPrimaryGreen),
                    SizedBox(width: 4),
                    Text(DateFormat('d MMM yyyy').format(DateTime.now()),
                        style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: kPrimaryGreen)),
                  ]),
                ),
                SizedBox(height: 2),
                Text('08:00 AM',
                    style: TextStyle(fontSize: 9, color: Colors.grey)),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Row(
      children: [
        Expanded(
          child: SizedBox(
            height: 46,
            child: TextField(
              controller: _searchCtrl,
              style: TextStyle(fontSize: 13),
              onSubmitted: _search,
              decoration: InputDecoration(
                hintText: 'Search item / چیز تلاش کریں',
                hintStyle: TextStyle(color: Colors.grey, fontSize: 13),
                prefixIcon: Icon(Icons.search, size: 20, color: Colors.grey),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(
                        icon:
                            Icon(Icons.close, size: 16, color: Colors.grey),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() {
                            _result = null;
                            _searched = false;
                          });
                        })
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: EdgeInsets.zero,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: Colors.grey.shade300),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: Colors.grey.shade300),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: kPrimaryGreen, width: 1.5),
                ),
              ),
            ),
          ),
        ),
        SizedBox(width: 8),
        SizedBox(
          height: 46,
          child: ElevatedButton.icon(
            onPressed: () => _search(_searchCtrl.text),
            icon: Icon(Icons.search, size: 18),
            label: Text('Search\nتلاش کریں',
                textAlign: TextAlign.center, style: TextStyle(fontSize: 11)),
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryGreen,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
              padding: EdgeInsets.symmetric(horizontal: 12),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPopularSearches() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Popular Searches / مقبول تلاشیں',
            style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
                color: Color(0xFF444444))),
        SizedBox(height: 8),
        Wrap(
          spacing: 8,
          runSpacing: 6,
          children: _popularSearches.map((s) {
            return GestureDetector(
              onTap: () {
                _searchCtrl.text = s;
                _search(s);
              },
              child: Container(
                padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: Text(s,
                    style:
                        TextStyle(fontSize: 12, color: Color(0xFF444444))),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _buildNoResult() {
    return Padding(
      padding: EdgeInsets.all(30),
      child: Center(
        child: Column(
          children: [
            Icon(Icons.search_off, size: 48, color: Colors.grey),
            SizedBox(height: 10),
            Text('Item not found\nچیز نہیں ملی',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    if (_result == null) return const SizedBox();
    final change = (_result!['price_change'] ?? 0.0).toDouble();
    final pct = (_result!['change_percent'] ?? 0.0).toDouble();
    final isUp = change > 0;

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.search, color: kPrimaryGreen, size: 16),
                SizedBox(width: 6),
                Text('Search Result / تلاش کا نتیجہ',
                    style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: kPrimaryGreen)),
              ],
            ),
            SizedBox(height: 12),
            Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(40),
                  child: _result!['image_url'] != null
                      ? Image.network(_result!['image_url'],
                          width: 72,
                          height: 72,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _placeholderImg())
                      : _placeholderImg(),
                ),
                SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(_result!['name'] ?? '',
                          style: TextStyle(
                              fontSize: 17, fontWeight: FontWeight.bold)),
                      if (_result!['name_urdu'] != null)
                        Text(_result!['name_urdu'],
                            style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey,
                                fontFamily: 'NotoNastaliqUrdu')),
                      SizedBox(height: 4),
                      Text(
                          'Unit: ${_result!['unit'] ?? '1 Kg'}'
                          '    اکائی: ${_result!['unit_urdu'] ?? '1 کلوگرام'}',
                          style:
                              TextStyle(fontSize: 11.5, color: Colors.grey)),
                    ],
                  ),
                ),
                ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 140),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text('Current Price / موجودہ قیمت',
                          textAlign: TextAlign.right,
                          style: TextStyle(fontSize: 10, color: Colors.grey)),
                      SizedBox(height: 4),
                      Text('Rs. ${(_result!['price'] ?? 0.0).toStringAsFixed(2)}',
                          style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1A1A1A))),
                      Text('Per Kg    فی کلوگرام',
                          style: TextStyle(fontSize: 10, color: Colors.grey)),
                      SizedBox(height: 6),
                      FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              isUp ? Icons.arrow_upward : Icons.arrow_downward,
                              size: 13,
                              color: isUp ? Colors.red : Colors.green,
                            ),
                            Text(
                              '${isUp ? '+' : ''}${change.toStringAsFixed(2)} (${pct.toStringAsFixed(2)}%)',
                              style: TextStyle(
                                  fontSize: 12,
                                  color: isUp ? Colors.red : Colors.green,
                                  fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      ),
                      Text('Compared to yesterday\nگزشتہ روز کے مقابلے میں',
                          textAlign: TextAlign.right,
                          style: TextStyle(fontSize: 9, color: Colors.grey)),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _placeholderImg() {
    return Container(
      width: 72,
      height: 72,
      decoration: BoxDecoration(
        color: kPrimaryGreen.withOpacity(0.1),
        shape: BoxShape.circle,
      ),
      child: Icon(Icons.image_outlined, color: kPrimaryGreen, size: 32),
    );
  }

  Widget _buildTrendChart() {
    if (_trendData.isEmpty) return const SizedBox();
    final prices =
        _trendData.map((d) => (d['price'] as num).toDouble()).toList();
    final minPrice = prices.reduce((a, b) => a < b ? a : b) - 5;
    final maxPrice = prices.reduce((a, b) => a > b ? a : b) + 5;
    final range = maxPrice - minPrice;

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Price Trend / قیمت کا رجحان',
                    style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: kPrimaryGreen)),
                _periodDropdown(),
              ],
            ),
            SizedBox(height: 16),
            SizedBox(
              height: 160,
              child: CustomPaint(
                size: Size(double.infinity, 160),
                painter: _TrendChartPainter(
                    data: _trendData,
                    minPrice: minPrice,
                    maxPrice: maxPrice,
                    range: range),
              ),
            ),
            SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: _trendData
                  .map((d) => Text(d['date'] ?? '',
                      style: TextStyle(fontSize: 9, color: Colors.grey)))
                  .toList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _periodDropdown() {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey.shade300),
        borderRadius: BorderRadius.circular(8),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: _trendPeriod,
          isDense: true,
          icon:
              Icon(Icons.keyboard_arrow_down, size: 16, color: Colors.grey),
          style: TextStyle(fontSize: 12, color: Color(0xFF333333)),
          items: ['Last 7 Days', 'Last 30 Days', 'Last 3 Months']
              .map((p) => DropdownMenuItem(value: p, child: Text(p)))
              .toList(),
          onChanged: (v) {
            if (v != null && _result != null) {
              setState(() => _trendPeriod = v);
              _loadTrend(_result!['id']);
            }
          },
        ),
      ),
    );
  }

  Widget _buildPriceDetails() {
    final avg = _priceDetails?['average'] ?? 144.0;
    final high = _priceDetails?['highest'] ?? 150.0;
    final highDate = _priceDetails?['highest_date'] ?? DateFormat('d MMM yyyy').format(DateTime.now());
    final low = _priceDetails?['lowest'] ?? 138.0;
    final lowDate = _priceDetails?['lowest_date'] ?? DateFormat('d MMM yyyy').format(DateTime.now());

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: EdgeInsets.fromLTRB(16, 14, 16, 10),
            child: Text('Price Details / تفصیلات',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: kPrimaryGreen)),
          ),
          _detailRow(
              'Average Price (7 Days)', 'Rs. ${avg.toStringAsFixed(2)}', '',
              isFirst: true),
          _detailRow('Highest Price (7 Days)', 'Rs. ${high.toStringAsFixed(2)}',
              highDate),
          _detailRow(
              'Lowest Price (7 Days)', 'Rs. ${low.toStringAsFixed(2)}', lowDate,
              isLast: true),
        ],
      ),
    );
  }

  Widget _detailRow(String label, String value, String date,
      {bool isFirst = false, bool isLast = false}) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        border: Border(
          top: isFirst
              ? BorderSide.none
              : BorderSide(color: Colors.grey.shade100),
        ),
      ),
      child: Row(
        children: [
          Expanded(
            flex: 3,
            child: Text(label,
                style: TextStyle(fontSize: 12.5, color: Color(0xFF444444))),
          ),
          Expanded(
            flex: 2,
            child: FittedBox(
              fit: BoxFit.scaleDown,
              alignment: Alignment.centerRight,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(value,
                      style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          fontFamily: 'NotoNastaliqUrdu')),
                  if (date.isNotEmpty) ...[
                    SizedBox(width: 10),
                    Text(date,
                        style: TextStyle(fontSize: 10.5, color: Colors.grey)),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNoteCard() {
    return Container(
      padding: EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: kPrimaryGreen.withOpacity(0.06),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: kPrimaryGreen.withOpacity(0.2)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: EdgeInsets.all(6),
            decoration: BoxDecoration(
                color: kPrimaryGreen, borderRadius: BorderRadius.circular(8)),
            child: Icon(Icons.info_outline, color: Colors.white, size: 14),
          ),
          SizedBox(width: 10),
          Expanded(
            child: Text(
              'Note:\nPrices are indicative and may vary depending on market.\n\nنوٹ: قیمتیں اشارتی ہیں اور مارکیٹ کے مطابق مختلف ہو سکتی ہیں۔',
              style: TextStyle(fontSize: 11.5, color: Color(0xFF444444)),
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────
//  Trend Line Chart Painter
// ─────────────────────────────────────────────
class _TrendChartPainter extends CustomPainter {
  final List<Map<String, dynamic>> data;
  final double minPrice;
  final double maxPrice;
  final double range;

  const _TrendChartPainter({
    required this.data,
    required this.minPrice,
    required this.maxPrice,
    required this.range,
  });

  @override
  void paint(Canvas canvas, Size size) {
    if (data.isEmpty || range == 0) return;

    final linePaint = Paint()
      ..color = kPrimaryGreen
      ..strokeWidth = 2.5
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final dotPaint = Paint()
      ..color = kPrimaryGreen
      ..style = PaintingStyle.fill;

    final fillPaint = Paint()
      ..color = kPrimaryGreen.withOpacity(0.08)
      ..style = PaintingStyle.fill;

    final gridPaint = Paint()
      ..color = Colors.grey.withOpacity(0.15)
      ..strokeWidth = 1;

    final textStyle =
        TextStyle(color: Colors.grey, fontSize: 9, fontFamily: 'sans-serif');

    final n = data.length;
    final xStep = size.width / (n - 1);

    // Grid lines (3 horizontal)
    for (int g = 0; g <= 3; g++) {
      final y = size.height * g / 3;
      canvas.drawLine(Offset(0, y), Offset(size.width, y), gridPaint);
      final price = maxPrice - (range * g / 3);
      final tp = TextPainter(
          text: TextSpan(text: price.toStringAsFixed(0), style: textStyle),
          textDirection: TextDirection.ltr)
        ..layout();
      tp.paint(canvas, Offset(0, y + 2));
    }

    // Y positions
    List<Offset> points = [];
    for (int i = 0; i < n; i++) {
      final price = (data[i]['price'] as num).toDouble();
      final x = i * xStep;
      final y = size.height * (1 - (price - minPrice) / range);
      points.add(Offset(x, y));
    }

    // Fill area
    final fillPath = Path()..moveTo(0, size.height);
    for (final p in points) {
      fillPath.lineTo(p.dx, p.dy);
    }
    fillPath.lineTo(size.width, size.height);
    fillPath.close();
    canvas.drawPath(fillPath, fillPaint);

    // Line
    final linePath = Path()..moveTo(points[0].dx, points[0].dy);
    for (int i = 1; i < points.length; i++) {
      linePath.lineTo(points[i].dx, points[i].dy);
    }
    canvas.drawPath(linePath, linePaint);

    // Dots + price labels
    for (int i = 0; i < points.length; i++) {
      canvas.drawCircle(points[i], 4, dotPaint);
      canvas.drawCircle(
          points[i],
          4,
          Paint()
            ..color = Colors.white
            ..style = PaintingStyle.stroke
            ..strokeWidth = 2);

      final price = (data[i]['price'] as num).toDouble();
      final tp = TextPainter(
          text: TextSpan(
              text: price.toStringAsFixed(2),
              style: TextStyle(
                  color: kPrimaryGreen,
                  fontSize: 9,
                  fontWeight: FontWeight.bold,
                  fontFamily: 'sans-serif')),
          textDirection: TextDirection.ltr)
        ..layout();
      tp.paint(canvas, Offset(points[i].dx - tp.width / 2, points[i].dy - 18));
    }
  }

  @override
  bool shouldRepaint(_) => true;
}
