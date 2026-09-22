CREATE TYPE "customer_status" AS ENUM (
  'New',
  'Existing'
);

CREATE TYPE "visit_type" AS ENUM (
  'Visit',
  'NonVisit'
);

CREATE TYPE "validation_status" AS ENUM (
  'Pending',
  'Valid',
  'Rejected'
);

CREATE TYPE "photo_type" AS ENUM (
  'LocationPhoto',
  'PhotoWithPIC'
);

CREATE TYPE "attendance_status" AS ENUM (
  'OnTime',
  'Late'
);

CREATE TYPE "day_type" AS ENUM (
  'Weekday',
  'Weekend',
  'Holiday'
);

CREATE TABLE "witel" (
  "id" integer PRIMARY KEY,
  "name" varchar
);

CREATE TABLE "telda" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "witel_id" integer
);

CREATE TABLE "role" (
  "id" integer PRIMARY KEY,
  "name" varchar
);

CREATE TABLE "activity_type" (
  "id" integer PRIMARY KEY,
  "name" varchar
);

CREATE TABLE "activity_category" (
  "id" integer PRIMARY KEY,
  "name" varchar
);

CREATE TABLE "r_level" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "sort_order" integer
);

CREATE TABLE "service_category" (
  "id" integer PRIMARY KEY,
  "name" varchar
);

CREATE TABLE "service" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "service_category_id" integer
);

CREATE TABLE "business_customer" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "nipnas" varchar,
  "status" customer_status,
  "telda_id" integer,
  "service_id" integer,
  "default_pic_name" varchar,
  "default_pic_contact" varchar,
  "address" varchar,
  "latitude" decimal,
  "longitude" decimal,
  "segment" varchar,
  "created_at" timestamp
);

CREATE TABLE "employee" (
  "id" integer PRIMARY KEY,
  "name" varchar,
  "email" varchar,
  "phone" varchar,
  "role_id" integer,
  "telda_id" integer,
  "is_active" boolean,
  "nip" varchar,
  "supervisor_id" integer
);

CREATE TABLE "visit_report" (
  "id" integer PRIMARY KEY,
  "employee_id" integer,
  "business_customer_id" integer,
  "customer_pic_name" varchar,
  "activity_type_id" integer,
  "r_level_id" integer,
  "activity_category_id" integer,
  "estimated_value" decimal,
  "activity_description" text,
  "action_plan" text,
  "voc" text,
  "visit_type" visit_type,
  "visit_date" date,
  "visit_time" time,
  "validation_status" validation_status,
  "validator_id" integer,
  "validation_notes" text,
  "validated_at" timestamp,
  "created_at" timestamp
);

CREATE TABLE "visit_report_service" (
  "id" integer PRIMARY KEY,
  "visit_report_id" integer,
  "service_id" integer
);

CREATE TABLE "report_location" (
  "id" integer PRIMARY KEY,
  "visit_report_id" integer,
  "latitude" decimal,
  "longitude" decimal,
  "accuracy_meters" decimal,
  "captured_at" timestamp
);

CREATE TABLE "report_photo" (
  "id" integer PRIMARY KEY,
  "visit_report_id" integer,
  "photo_type" photo_type,
  "file_url" varchar,
  "uploaded_at" timestamp,
  "latitude" decimal,
  "longitude" decimal,
  "file_size" integer
);

CREATE TABLE "attendance" (
  "id" integer PRIMARY KEY,
  "employee_id" integer,
  "date" date,
  "check_in_time" time,
  "check_out_time" time,
  "status" attendance_status,
  "late_minutes" integer,
  "day_type" day_type,
  "is_mandatory" boolean,
  "notes" text
);

COMMENT ON TABLE "witel" IS 'Unit wilayah Telkom (regional), induk dari beberapa telda';

COMMENT ON COLUMN "witel"."id" IS 'ID unik witel';

COMMENT ON COLUMN "witel"."name" IS 'Nama witel, misal: Jakarta Outer';

COMMENT ON TABLE "telda" IS 'Wilayah kerja Telkom yang lebih kecil, berada di bawah satu witel';

COMMENT ON COLUMN "telda"."id" IS 'ID unik telda';

COMMENT ON COLUMN "telda"."name" IS 'Nama telda, misal: PSM, KBY, RMG, TBE, JTN, PGG, GAN, CPE';

COMMENT ON COLUMN "telda"."witel_id" IS 'Witel induk dari telda ini';

COMMENT ON TABLE "role" IS 'Daftar peran yang bisa dimiliki seorang pegawai';

COMMENT ON COLUMN "role"."id" IS 'ID unik role';

COMMENT ON COLUMN "role"."name" IS 'Nama role: AM, AR, Biasa/Regular, atau Validator/Supervisor';

COMMENT ON TABLE "activity_type" IS 'Jenis kegiatan kunjungan, muncul di breakdown dashboard Kinerja';

COMMENT ON COLUMN "activity_type"."id" IS 'ID unik jenis kegiatan';

COMMENT ON COLUMN "activity_type"."name" IS 'misal: Demo, Visit Perdana (First Visit)';

COMMENT ON TABLE "activity_category" IS 'Kategori aktivitas dalam funnel penjualan — field wajib (Kategori *) di form';

COMMENT ON COLUMN "activity_category"."id" IS 'ID unik kategori aktivitas';

COMMENT ON COLUMN "activity_category"."name" IS 'Approaching, Dealing, atau Aftersales';

COMMENT ON TABLE "r_level" IS 'Tingkat/level kunjungan (R-Level) — field wajib di form';

COMMENT ON COLUMN "r_level"."id" IS 'ID unik R-Level';

COMMENT ON COLUMN "r_level"."name" IS 'nama/label level R';

COMMENT ON COLUMN "r_level"."sort_order" IS 'urutan tampil, dari level terendah ke tertinggi';

COMMENT ON TABLE "service_category" IS 'Kategori besar dari layanan Telkom, dipakai untuk mengelompokkan tabel service';

COMMENT ON COLUMN "service_category"."id" IS 'ID unik kategori layanan';

COMMENT ON COLUMN "service_category"."name" IS 'Connectivity, Platform, atau Service';

COMMENT ON TABLE "service" IS 'Katalog layanan yang bisa ditawarkan/dipilih saat kunjungan';

COMMENT ON COLUMN "service"."id" IS 'ID unik layanan';

COMMENT ON COLUMN "service"."name" IS 'misal: Astinet, Indibiz, IP Transit, Metro-E, Neucentrix, Collocation, SIP Trunk, Lainnya';

COMMENT ON COLUMN "service"."service_category_id" IS 'kategori dari layanan ini';

COMMENT ON TABLE "business_customer" IS 'Data Business Customer (BC) / pelanggan yang dikunjungi';

COMMENT ON COLUMN "business_customer"."id" IS 'ID unik BC';

COMMENT ON COLUMN "business_customer"."name" IS 'Nama BC / perusahaan pelanggan';

COMMENT ON COLUMN "business_customer"."nipnas" IS 'Nomor identitas pelanggan Telkom (NIPNAS)';

COMMENT ON COLUMN "business_customer"."status" IS 'status pelanggan: Baru atau Eksisting';

COMMENT ON COLUMN "business_customer"."telda_id" IS 'telda tempat BC ini berada';

COMMENT ON COLUMN "business_customer"."service_id" IS 'layanan utama yang terkait BC ini (menggantikan konsep ekosistem lama)';

COMMENT ON COLUMN "business_customer"."default_pic_name" IS 'nama PIC utama yang biasa dihubungi di perusahaan ini';

COMMENT ON COLUMN "business_customer"."default_pic_contact" IS 'kontak telepon/email PIC utama';

COMMENT ON COLUMN "business_customer"."address" IS 'alamat kantor BC';

COMMENT ON COLUMN "business_customer"."latitude" IS 'koordinat resmi lokasi BC (lintang), dipakai untuk validasi jarak GPS kunjungan';

COMMENT ON COLUMN "business_customer"."longitude" IS 'koordinat resmi lokasi BC (bujur)';

COMMENT ON COLUMN "business_customer"."segment" IS 'segmen/klasifikasi pelanggan';

COMMENT ON COLUMN "business_customer"."created_at" IS 'kapan data BC ini pertama kali dicatat';

COMMENT ON TABLE "employee" IS 'Data pegawai (bisa berperan AM, AR, atau role lain) yang membuat laporan kunjungan';

COMMENT ON COLUMN "employee"."id" IS 'ID unik pegawai';

COMMENT ON COLUMN "employee"."name" IS 'nama pegawai';

COMMENT ON COLUMN "employee"."email" IS 'email pegawai, dipakai untuk login';

COMMENT ON COLUMN "employee"."phone" IS 'nomor telepon/WA pegawai';

COMMENT ON COLUMN "employee"."role_id" IS 'peran pegawai ini: AM/AR/Biasa/Validator';

COMMENT ON COLUMN "employee"."telda_id" IS 'telda tempat pegawai ini bertugas';

COMMENT ON COLUMN "employee"."is_active" IS 'status aktif pegawai';

COMMENT ON COLUMN "employee"."nip" IS 'nomor induk pegawai';

COMMENT ON COLUMN "employee"."supervisor_id" IS 'atasan langsung pegawai ini, dipakai untuk alur validasi laporan';

COMMENT ON TABLE "visit_report" IS 'Tabel inti — satu baris mewakili satu laporan kunjungan/aktivitas';

COMMENT ON COLUMN "visit_report"."id" IS 'ID unik laporan';

COMMENT ON COLUMN "visit_report"."employee_id" IS 'pegawai yang membuat laporan ini';

COMMENT ON COLUMN "visit_report"."business_customer_id" IS 'BC yang dikunjungi';

COMMENT ON COLUMN "visit_report"."customer_pic_name" IS 'nama PIC pelanggan saat kunjungan ini, bisa beda dari default_pic_name di business_customer';

COMMENT ON COLUMN "visit_report"."activity_type_id" IS 'jenis kegiatan kunjungan ini';

COMMENT ON COLUMN "visit_report"."r_level_id" IS 'R-Level kunjungan ini';

COMMENT ON COLUMN "visit_report"."activity_category_id" IS 'kategori aktivitas: Approaching/Dealing/Aftersales';

COMMENT ON COLUMN "visit_report"."estimated_value" IS 'estimasi nilai transaksi dari kunjungan ini (Rp)';

COMMENT ON COLUMN "visit_report"."activity_description" IS 'deskripsi cerita yang terjadi saat kunjungan';

COMMENT ON COLUMN "visit_report"."action_plan" IS 'langkah selanjutnya setelah kunjungan ini';

COMMENT ON COLUMN "visit_report"."voc" IS 'Voice of Customer — keluhan/masukan yang disampaikan pelanggan';

COMMENT ON COLUMN "visit_report"."visit_type" IS 'apakah ini kunjungan langsung (Visit) atau bukan (Non-Visit)';

COMMENT ON COLUMN "visit_report"."visit_date" IS 'tanggal kunjungan';

COMMENT ON COLUMN "visit_report"."visit_time" IS 'jam kunjungan';

COMMENT ON COLUMN "visit_report"."validation_status" IS 'status persetujuan laporan: Pending/Valid/Rejected';

COMMENT ON COLUMN "visit_report"."validator_id" IS 'pegawai (biasanya atasan) yang memvalidasi laporan ini';

COMMENT ON COLUMN "visit_report"."validation_notes" IS 'catatan dari validator, misal alasan ditolak';

COMMENT ON COLUMN "visit_report"."validated_at" IS 'waktu laporan ini divalidasi';

COMMENT ON COLUMN "visit_report"."created_at" IS 'waktu laporan ini dibuat/dikirim';

COMMENT ON TABLE "visit_report_service" IS 'Tabel junction — satu laporan kunjungan bisa punya banyak layanan yang dicentang (checkbox multi-pilih)';

COMMENT ON COLUMN "visit_report_service"."id" IS 'ID unik baris';

COMMENT ON COLUMN "visit_report_service"."visit_report_id" IS 'laporan kunjungan terkait';

COMMENT ON COLUMN "visit_report_service"."service_id" IS 'layanan yang dicentang pada laporan ini';

COMMENT ON TABLE "report_location" IS 'Riwayat titik GPS yang diambil untuk satu laporan — opsional, hanya perlu kalau mau simpan histori tombol Perbarui Lokasi';

COMMENT ON COLUMN "report_location"."id" IS 'ID unik titik lokasi';

COMMENT ON COLUMN "report_location"."visit_report_id" IS 'laporan kunjungan terkait';

COMMENT ON COLUMN "report_location"."latitude" IS 'koordinat GPS saat kunjungan (lintang)';

COMMENT ON COLUMN "report_location"."longitude" IS 'koordinat GPS saat kunjungan (bujur)';

COMMENT ON COLUMN "report_location"."accuracy_meters" IS 'akurasi GPS dalam meter';

COMMENT ON COLUMN "report_location"."captured_at" IS 'waktu titik GPS ini diambil';

COMMENT ON TABLE "report_photo" IS 'Foto-foto yang dilampirkan di satu laporan kunjungan';

COMMENT ON COLUMN "report_photo"."id" IS 'ID unik foto';

COMMENT ON COLUMN "report_photo"."visit_report_id" IS 'laporan kunjungan terkait';

COMMENT ON COLUMN "report_photo"."photo_type" IS 'jenis foto: Foto Lokasi atau Foto dengan PIC';

COMMENT ON COLUMN "report_photo"."file_url" IS 'lokasi file foto tersimpan';

COMMENT ON COLUMN "report_photo"."uploaded_at" IS 'waktu foto diunggah';

COMMENT ON COLUMN "report_photo"."latitude" IS 'koordinat dari metadata EXIF foto (kalau ada), untuk cross-check tambahan';

COMMENT ON COLUMN "report_photo"."longitude" IS 'koordinat dari metadata EXIF foto (kalau ada)';

COMMENT ON COLUMN "report_photo"."file_size" IS 'ukuran file foto';

COMMENT ON TABLE "attendance" IS 'Data absensi harian pegawai (check-in/check-out), terpisah dari laporan kunjungan';

COMMENT ON COLUMN "attendance"."id" IS 'ID unik baris absensi';

COMMENT ON COLUMN "attendance"."employee_id" IS 'pegawai yang absen';

COMMENT ON COLUMN "attendance"."date" IS 'tanggal absensi';

COMMENT ON COLUMN "attendance"."check_in_time" IS 'jam check-in';

COMMENT ON COLUMN "attendance"."check_out_time" IS 'jam check-out';

COMMENT ON COLUMN "attendance"."status" IS 'On Time atau Late';

COMMENT ON COLUMN "attendance"."late_minutes" IS 'jumlah menit keterlambatan';

COMMENT ON COLUMN "attendance"."day_type" IS 'Weekday/Weekend/Holiday';

COMMENT ON COLUMN "attendance"."is_mandatory" IS 'apakah absen wajib di hari ini';

COMMENT ON COLUMN "attendance"."notes" IS 'keterangan tambahan, misal alasan telat/izin';

ALTER TABLE "telda" ADD FOREIGN KEY ("witel_id") REFERENCES "witel" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "service" ADD FOREIGN KEY ("service_category_id") REFERENCES "service_category" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "business_customer" ADD FOREIGN KEY ("telda_id") REFERENCES "telda" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "business_customer" ADD FOREIGN KEY ("service_id") REFERENCES "service" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "employee" ADD FOREIGN KEY ("role_id") REFERENCES "role" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "employee" ADD FOREIGN KEY ("telda_id") REFERENCES "telda" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "employee" ADD FOREIGN KEY ("supervisor_id") REFERENCES "employee" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report" ADD FOREIGN KEY ("employee_id") REFERENCES "employee" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report" ADD FOREIGN KEY ("business_customer_id") REFERENCES "business_customer" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report" ADD FOREIGN KEY ("activity_type_id") REFERENCES "activity_type" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report" ADD FOREIGN KEY ("r_level_id") REFERENCES "r_level" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report" ADD FOREIGN KEY ("activity_category_id") REFERENCES "activity_category" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report" ADD FOREIGN KEY ("validator_id") REFERENCES "employee" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report_service" ADD FOREIGN KEY ("visit_report_id") REFERENCES "visit_report" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "visit_report_service" ADD FOREIGN KEY ("service_id") REFERENCES "service" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "report_location" ADD FOREIGN KEY ("visit_report_id") REFERENCES "visit_report" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "report_photo" ADD FOREIGN KEY ("visit_report_id") REFERENCES "visit_report" ("id") DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE "attendance" ADD FOREIGN KEY ("employee_id") REFERENCES "employee" ("id") DEFERRABLE INITIALLY IMMEDIATE;
