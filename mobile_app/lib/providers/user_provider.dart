import 'package:flutter/material.dart';
import '../models/user_profile.dart';

class UserProvider with ChangeNotifier {
  UserProfile _user = UserProfile(
    name: 'สมชาย เข็มกลัด',
    email: 'user@example.com',
    phone: '081-234-5678',
    address: '123/45 หมู่ 3',
    subDistrict: 'บางมด',
    district: 'ทุ่งครุ',
    province: 'กรุงเทพมหานคร',
    zipcode: '10140',
  );

  UserProfile get user => _user;

  void updateProfile({
    required String name,
    required String phone,
    required String address,
    required String subDistrict,
    required String district,
    required String province,
    required String zipcode,
  }) {
    _user.name = name;
    _user.phone = phone;
    _user.address = address;
    _user.subDistrict = subDistrict;
    _user.district = district;
    _user.province = province;
    _user.zipcode = zipcode;
    notifyListeners();
  }
}
