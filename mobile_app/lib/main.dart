import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/product_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/user_provider.dart';
import 'providers/order_provider.dart';
import 'screens/main_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const OnlySportApp());
}

class OnlySportApp extends StatelessWidget {
  const OnlySportApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    const crimsonRed = Color(0xFFD32F2F);
    const backgroundCream = Color(0xFFFDFDF5);

    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => ProductProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => UserProvider()),
        ChangeNotifierProvider(create: (_) => OrderProvider()),
      ],
      child: MaterialApp(
        title: 'OnlySport',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          fontFamily: 'Kanit',
          fontFamilyFallback: const [
            'Kanit',
            'Sarabun',
            'Leelawadee UI',
            'Leelawadee',
            'Noto Sans Thai',
            'Tahoma',
            'sans-serif',
          ],
          colorScheme: ColorScheme.fromSeed(
            seedColor: crimsonRed,
            primary: crimsonRed,
            background: backgroundCream,
            surface: Colors.white,
          ),
          scaffoldBackgroundColor: backgroundCream,
          appBarTheme: const AppBarTheme(
            backgroundColor: crimsonRed,
            foregroundColor: Colors.white,
            elevation: 0,
            iconTheme: IconThemeData(color: Colors.white),
            titleTextStyle: TextStyle(
              fontFamily: 'Kanit',
              fontFamilyFallback: ['Sarabun', 'Leelawadee UI', 'sans-serif'],
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          cardTheme: CardThemeData(
            color: Colors.white,
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(
                color: Colors.black.withOpacity(0.08),
                width: 1,
              ),
            ),
          ),
          floatingActionButtonTheme: const FloatingActionButtonThemeData(
            backgroundColor: crimsonRed,
            foregroundColor: Colors.white,
          ),
        ),
        home: const MainScreen(),
      ),
    );
  }
}
