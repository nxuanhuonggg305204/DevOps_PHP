<?php

namespace App\Http\Controllers;

use App\Models\NhanVien; 
use App\Models\BangCap;
use App\Models\ChuyenMon;
use App\Models\NgoaiNgu;
use App\Models\ThuongPhat;
use App\Models\NhanLuong;
use App\Models\UngLuong;  
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tongSoNhanVienDangLamViec = NhanVien::where('trangthai', 1)->count();

        $ngayBatDau30NgayQua = Carbon::now()->subDays(30)->startOfDay();
        $soLuongNhanVienMoi = NhanVien::where('created_at', '>=', $ngayBatDau30NgayQua)->count();

        $soLuongNhanVienNghiViec = NhanVien::where('trangthai', '!=', 1) // Hoặc where('trangthai', 0) nếu bạn chắc chắn
                                          ->whereNotNull('ngaynghilam') // Đảm bảo có ngày nghỉ làm
                                          ->where('ngaynghilam', '>=', $ngayBatDau30NgayQua)
                                          ->count();

        $nhanVienTheoBangCapData = NhanVien::join('bangcap', 'nhanvien.bangcap_id', '=', 'bangcap.id')
            ->where('nhanvien.trangthai', 1) // Chỉ tính nhân viên đang làm việc
            ->select('bangcap.tenbc as ten', DB::raw('count(nhanvien.id) as soluong'))
            ->groupBy('bangcap.tenbc')
            ->orderBy('soluong', 'desc')
            ->get();

        $nhanVienTheoChuyenMonData = NhanVien::join('chuyenmon', 'nhanvien.chuyenmon_id', '=', 'chuyenmon.id')
            ->where('nhanvien.trangthai', 1)
            ->select('chuyenmon.tencm as ten', DB::raw('count(nhanvien.id) as soluong'))
            ->groupBy('chuyenmon.tencm')
            ->orderBy('soluong', 'desc')
            ->get();
        
        $nhanVienTheoNgoaiNguData = NhanVien::join('ngoaingu', 'nhanvien.ngoaingu_id', '=', 'ngoaingu.id')
            ->where('nhanvien.trangthai', 1)
            ->select('ngoaingu.tenng as ten', DB::raw('count(nhanvien.id) as soluong'))
            ->groupBy('ngoaingu.tenng')
            ->orderBy('soluong', 'desc')
            ->get();
        
        $giaTriLoaiThuong = 1; 
        $giaTriLoaiPhat = 0;  

        $topThuong = ThuongPhat::with('nhanvien:id,hovaten') 
            ->select('nhanvien_id', DB::raw('count(*) as so_lan_thuong'))
            ->where('loai', $giaTriLoaiThuong)
            ->groupBy('nhanvien_id')
            ->orderBy('so_lan_thuong', 'desc')
            ->take(3) 
            ->get();

        $topPhat = ThuongPhat::with('nhanvien:id,hovaten')
            ->select('nhanvien_id', DB::raw('count(*) as so_lan_phat'))
            ->where('loai', $giaTriLoaiPhat)
            ->groupBy('nhanvien_id')
            ->orderBy('so_lan_phat', 'desc')
            ->take(3)
            ->get();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

       
        $soNhanVienDaThanhToanLuong = NhanLuong::where('thang', $currentMonth)
                                             ->where('nam', $currentYear)
                                             ->distinct('nhanvien_id') 
                                             ->count('nhanvien_id');

        
        $tongTienUngLuongThangNay = UngLuong::where('thang', $currentMonth)
                                          ->where('nam', $currentYear)
                                          ->sum('sotien'); 

        return Inertia::render('Dashboard/Index', [
            'tongSoNhanVienDangLamViec' => $tongSoNhanVienDangLamViec,
            'soLuongNhanVienMoi' => $soLuongNhanVienMoi,
            'soLuongNhanVienNghiViec' => $soLuongNhanVienNghiViec,
            'nhanVienTheoBangCap' => $nhanVienTheoBangCapData,
            'nhanVienTheoChuyenMon' => $nhanVienTheoChuyenMonData,
            'nhanVienTheoNgoaiNgu' => $nhanVienTheoNgoaiNguData,
            'topNhanVienThuong' => $topThuong,
            'topNhanVienPhat' => $topPhat,
            'soNhanVienDaThanhToanLuong' => $soNhanVienDaThanhToanLuong,
            'tongTienUngLuongThangNay' => $tongTienUngLuongThangNay ?? 0,
        ]);
    }
}
