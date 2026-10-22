class ComplaintModel {
  final int id;
  final String complaintNumber;
  final String fullName;
  final String cnic;
  final String mobile;
  final String itemName;
  final String shopName;
  final double? latitude;
  final double? longitude;
  final String locationAddress;
  final String details;
  final String status;
  final String? adminRemarks;
  final List<String> pictures;
  final String createdDate;
  final String submittedAt;

  const ComplaintModel({
    required this.id, required this.complaintNumber, required this.fullName,
    required this.cnic, required this.mobile, required this.itemName,
    required this.shopName, this.latitude, this.longitude,
    required this.locationAddress, required this.details, required this.status,
    this.adminRemarks, required this.pictures,
    required this.createdDate, required this.submittedAt,
  });

  factory ComplaintModel.fromJson(Map<String, dynamic> json) => ComplaintModel(
    id: json['id'],
    complaintNumber: json['complaint_number'] ?? '',
    fullName: json['full_name'] ?? '',
    cnic: json['cnic'] ?? '',
    mobile: json['mobile'] ?? '',
    itemName: json['item_name'] ?? '',
    shopName: json['shop_name'] ?? '',
    latitude: json['latitude']?.toDouble(),
    longitude: json['longitude']?.toDouble(),
    locationAddress: json['location_address'] ?? '',
    details: json['details'] ?? '',
    status: json['status'] ?? 'pending',
    adminRemarks: json['admin_remarks'],
    pictures: List<String>.from(json['pictures'] ?? []),
    createdDate: json['created_date'] ?? '',
    submittedAt: json['submitted_at'] ?? '',
  );
}
