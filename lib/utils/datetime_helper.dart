import 'package:intl/intl.dart';

class DatetimeHelper {
  static String get date {
    return DateFormat('yyyy-MM-dd').format(DateTime.now());
  }

  static String get time {
    return DateFormat('HH:mm:ss').format(DateTime.now());
  }

  static String get datetime {
    return DateFormat('yyyy-MM-dd HH:mm:ss').format(DateTime.now());
  }
}
