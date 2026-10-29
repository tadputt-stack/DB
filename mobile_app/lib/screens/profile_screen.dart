import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/order_provider.dart';
import '../providers/user_provider.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  void _showEditProfileModal(BuildContext context) {
    const crimsonRed = Color(0xFFD32F2F);
    final userProvider = context.read<UserProvider>();
    final user = userProvider.user;

    final nameController = TextEditingController(text: user.name);
    final phoneController = TextEditingController(text: user.phone);
    final addressController = TextEditingController(text: user.address);
    final subDistrictController = TextEditingController(text: user.subDistrict);
    final districtController = TextEditingController(text: user.district);
    final provinceController = TextEditingController(text: user.province);
    final zipcodeController = TextEditingController(text: user.zipcode);

    showDialog(
      context: context,
      builder: (BuildContext dialogContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: const Row(
            children: [
              Icon(Icons.edit, color: crimsonRed),
              SizedBox(width: 8),
              Text(
                'แก้ไขข้อมูลโปรไฟล์',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameController,
                  decoration: const InputDecoration(
                    labelText: 'ชื่อ - นามสกุล',
                    prefixIcon: Icon(Icons.person),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    labelText: 'เบอร์โทรศัพท์',
                    prefixIcon: Icon(Icons.phone),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: addressController,
                  decoration: const InputDecoration(
                    labelText: 'ที่อยู่ (บ้านเลขที่ / หมู่)',
                    prefixIcon: Icon(Icons.home),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: subDistrictController,
                  decoration: const InputDecoration(
                    labelText: 'ตำบล / แขวง',
                    prefixIcon: Icon(Icons.location_city),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: districtController,
                  decoration: const InputDecoration(
                    labelText: 'อำเภอ / เขต',
                    prefixIcon: Icon(Icons.map),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: provinceController,
                  decoration: const InputDecoration(
                    labelText: 'จังหวัด',
                    prefixIcon: Icon(Icons.landscape),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: zipcodeController,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'รหัสไปรษณีย์',
                    prefixIcon: Icon(Icons.markunread_mailbox),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogContext),
              style: TextButton.styleFrom(foregroundColor: Colors.grey[700]),
              child: const Text('ยกเลิก'),
            ),
            ElevatedButton(
              onPressed: () {
                userProvider.updateProfile(
                  name: nameController.text.trim(),
                  phone: phoneController.text.trim(),
                  address: addressController.text.trim(),
                  subDistrict: subDistrictController.text.trim(),
                  district: districtController.text.trim(),
                  province: provinceController.text.trim(),
                  zipcode: zipcodeController.text.trim(),
                );
                Navigator.pop(dialogContext);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว'),
                    backgroundColor: crimsonRed,
                    behavior: SnackBarBehavior.floating,
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: crimsonRed,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: const Text('บันทึก'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    const crimsonRed = Color(0xFFD32F2F);
    final user = context.watch<UserProvider>().user;
    final orderHistory = context.watch<OrderProvider>().orderHistory;

    return Scaffold(
      backgroundColor: const Color(0xFFFDFDF5),
      appBar: AppBar(
        backgroundColor: crimsonRed,
        elevation: 0,
        centerTitle: true,
        title: const Text(
          'โปรไฟล์',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: Colors.black.withOpacity(0.08),
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 30,
                        backgroundColor: crimsonRed.withOpacity(0.12),
                        child: const Icon(
                          Icons.person,
                          size: 36,
                          color: crimsonRed,
                        ),
                      ),
                      const SizedBox(width: 14),

                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user.name,
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: Colors.black87,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(
                                  Icons.phone_outlined,
                                  size: 14,
                                  color: Colors.grey,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  user.phone,
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: Colors.grey[700],
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      IconButton(
                        icon: const Icon(
                          Icons.edit,
                          color: crimsonRed,
                        ),
                        onPressed: () => _showEditProfileModal(context),
                      ),
                    ],
                  ),
                  const Divider(height: 24),

                  const Row(
                    children: [
                      Icon(Icons.location_on_outlined, size: 18, color: crimsonRed),
                      SizedBox(width: 6),
                      Text(
                        'ที่อยู่จัดส่ง',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Padding(
                    padding: const EdgeInsets.only(left: 24),
                    child: Text(
                      user.fullAddress,
                      style: const TextStyle(
                        fontSize: 14,
                        color: Colors.black54,
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),

            const Row(
              children: [
                Icon(Icons.history, color: crimsonRed),
                SizedBox(width: 8),
                Text(
                  'รายการสั่งซื้อ (Order History)',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black87,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            orderHistory.isEmpty
                ? Container(
                    padding: const EdgeInsets.all(24),
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: Colors.black.withOpacity(0.08),
                        width: 1,
                      ),
                    ),
                    child: const Center(
                      child: Text(
                        'ยังไม่มีประวัติการสั่งซื้อ',
                        style: TextStyle(color: Colors.grey, fontSize: 15),
                      ),
                    ),
                  )
                : ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: orderHistory.length,
                    itemBuilder: (context, orderIndex) {
                      final order = orderHistory[orderIndex];

                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: Colors.black.withOpacity(0.08),
                            width: 1,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'ออเดอร์ #${order.orderId ?? orderIndex + 1}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 15,
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.green[50],
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: Colors.green),
                                  ),
                                  child: Text(
                                    order.status,
                                    style: const TextStyle(
                                      color: Colors.green,
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const Divider(height: 16),

                            ...order.items.map((item) {
                              return Padding(
                                padding: const EdgeInsets.symmetric(vertical: 4),
                                child: Row(
                                  children: [
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(8),
                                      child: SizedBox(
                                        width: 50,
                                        height: 50,
                                        child: Image.network(
                                          item.imageUrl ??
                                              'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=300',
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, __, ___) => Container(
                                            color: Colors.grey[200],
                                            child: const Icon(
                                              Icons.sports_soccer,
                                              size: 24,
                                              color: Colors.grey,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            item.productName,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w600,
                                              fontSize: 13,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          Text(
                                            '฿${item.price.toStringAsFixed(0)} x ${item.quantity}',
                                            style: TextStyle(
                                              fontSize: 12,
                                              color: Colors.grey[700],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Text(
                                      '฿${item.subtotal.toStringAsFixed(0)}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            }).toList(),

                            const Divider(height: 16),

                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text(
                                  'ราคารวม:',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                  ),
                                ),
                                Text(
                                  '฿${order.totalPrice.toStringAsFixed(0)}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w800,
                                    fontSize: 16,
                                    color: crimsonRed,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
          ],
        ),
      ),
    );
  }
}
