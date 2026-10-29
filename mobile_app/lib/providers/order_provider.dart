import 'package:flutter/material.dart';
import '../models/order.dart';
import '../models/user_profile.dart';
import '../services/api_service.dart';
import 'cart_provider.dart';

class OrderProvider with ChangeNotifier {
  final List<OrderModel> _orderHistory = [];
  bool _isLoading = false;

  List<OrderModel> get orderHistory => List.unmodifiable(_orderHistory);
  bool get isLoading => _isLoading;

  OrderProvider() {
    loadOrders();
  }

  Future<void> loadOrders() async {
    _isLoading = true;
    notifyListeners();

    try {
      final orders = await ApiService.fetchOrders();
      _orderHistory.clear();
      _orderHistory.addAll(orders);
      debugPrint('[OrderProvider] Loaded ${_orderHistory.length} orders from MySQL');
    } catch (e) {
      debugPrint('[OrderProvider] Error loading order history: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> placeOrder(CartProvider cartProvider, UserProfile user) async {
    if (cartProvider.items.isEmpty) return false;

    final orderItems = cartProvider.items
        .map((ci) => OrderItemModel(
              productId: ci.product.id,
              productName: ci.product.name,
              size: ci.size,
              quantity: ci.quantity,
              price: ci.product.price,
              subtotal: ci.totalPrice,
              imageUrl: ci.product.imageUrl,
            ))
        .toList();

    final newOrder = OrderModel(
      customerName: user.name,
      customerEmail: user.email,
      totalPrice: cartProvider.totalPrice,
      status: 'สำเร็จ',
      createdAt: DateTime.now(),
      items: orderItems,
    );

    final success = await ApiService.createOrder(newOrder);

    if (success) {
      await loadOrders();
      cartProvider.clearCart();
      notifyListeners();
      return true;
    }
    return false;
  }
}
