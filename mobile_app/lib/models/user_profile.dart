class UserProfile {
  String name;
  String email;
  String phone;
  String address;
  String subDistrict;
  String district;
  String province;
  String zipcode;
  String avatarUrl;

  UserProfile({
    required this.name,
    required this.email,
    required this.phone,
    required this.address,
    required this.subDistrict,
    required this.district,
    required this.province,
    required this.zipcode,
    this.avatarUrl = '',
  });

  String get fullAddress {
    List<String> parts = [];
    if (address.isNotEmpty) parts.add(address);
    if (subDistrict.isNotEmpty) parts.add('ต.$subDistrict');
    if (district.isNotEmpty) parts.add('อ.$district');
    if (province.isNotEmpty) parts.add('จ.$province');
    if (zipcode.isNotEmpty) parts.add(zipcode);
    return parts.join(' ');
  }
}
