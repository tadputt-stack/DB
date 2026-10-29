import 'package:flutter/material.dart';
import '../models/cart_item.dart';
import '../models/product.dart';

class CartProvider with ChangeNotifier {
  final List<CartItem> _items = [];

  List<CartItem> get items => List.unmodifiable(_items);

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get totalPrice => _items.fold(0.0, (sum, item) => sum + item.totalPrice);

  void addToCart(Product product, {int quantity = 1, String size = 'US 9'}) {
    final existingIndex = _items.indexWhere(
      (item) => item.product.id == product.id && item.size == size,
    );

    if (existingIndex >= 0) {
      _items[existingIndex].quantity += quantity;
    } else {
      _items.add(CartItem(
        product: product,
        quantity: quantity,
        size: size,
      ));
    }
    notifyListeners();
  }

  void removeFromCart(CartItem item) {
    _items.removeWhere((element) =>
        element.product.id == item.product.id && element.size == item.size);
    notifyListeners();
  }

  void updateQuantity(CartItem item, int delta) {
    final index = _items.indexWhere((element) =>
        element.product.id == item.product.id && element.size == item.size);

    if (index >= 0) {
      final newQty = _items[index].quantity + delta;
      if (newQty <= 0) {
        _items.removeAt(index);
      } else {
        _items[index].quantity = newQty;
      }
      notifyListeners();
    }
  }

  void clearCart() {
    _items.clear();
    notifyListeners();
  }
}
