enum OfficerStatus { offline, ready, bertugas, arrived, selesai }

extension OfficerStatusText on OfficerStatus {
  String get text {
    switch (this) {
      case OfficerStatus.offline:
        return "OFFLINE";
      case OfficerStatus.ready:
        return "READY";
      case OfficerStatus.bertugas:
        return "BERTUGAS";
      case OfficerStatus.arrived:
        return "TIBA DI TKP";
      case OfficerStatus.selesai:
        return "SELESAI";
    }
  }
}
