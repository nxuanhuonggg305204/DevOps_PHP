<template>
  <div>
    <portal-target name="dropdown" slim />
    <div class="md:flex md:flex-col">
      <div class="md:h-screen md:flex md:flex-col">
        <div class="md:flex md:flex-shrink-0">
          <div class="bg-indigo-900 md:flex-shrink-0 md:w-64 px-6 py-4 flex items-center justify-between md:justify-center">
            <inertia-link class="mt-1" href="/">
              <logo class="fill-white" width="120" height="28" />
            </inertia-link>
            <dropdown class="md:hidden" placement="bottom-end" :auto-close="false">
              <svg class="fill-white w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" /></svg>
              <div slot="dropdown" class="mt-2 px-8 py-4 shadow-lg bg-indigo-800 rounded">
                <main-menu />
              </div>
            </dropdown>
          </div>
          <div class="bg-indigo-400 border-b w-full p-4 md:py-0 md:px-12 text-sm md:text-md flex justify-between items-center">
            <div class="mt-1 mr-4 font-semibold text-white">Công Ty Phần Mềm CLOK</div>
            <dropdown class="mt-1" placement="bottom-end">
              <div class="flex items-center cursor-pointer select-none group">
                <div class="text-white group-hover:text-white focus:text-white mr-1 whitespace-nowrap">
                  <span>{{ $page.props.auth.user.hovaten }}</span>
                </div>
                <icon class="w-5 h-5 fill-white" name="cheveron-down" />
              </div>
              <div slot="dropdown" class="mt-2 py-2 shadow-xl bg-white rounded text-sm">
                <inertia-link class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('nhanvien.edit', $page.props.auth.user.id)">Hồ Sơ Của Bạn</inertia-link>
                <inertia-link class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('chamcong', { nhanvien: $page.props.auth.user.nhanvien_id })">Lịch Sử Chấm Công</inertia-link>
                <inertia-link class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('users.edit', $page.props.auth.user.id)">Cài Đặt Tài Khoản</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('phongban')">Quản Lý Phòng Ban</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('chucvu')">Quản Lý Chức Vụ</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('bangcap')">Quản Lý Bằng Cấp</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('chuyenmon')">Quản Lý Chuyên Môn</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('ngoaingu')">Quản Lý Ngoại Ngữ</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('dantoc')">Quản Lý Dân Tộc</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('tongiao')">Quản Lý Tôn Giáo</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('loaibaohiem')">Quản Lý Loại Bảo Hiểm</inertia-link>
                <inertia-link v-if="$page.props.auth.user.role == 2" class="block px-6 py-2 hover:bg-indigo-500 hover:text-white" :href="route('users')">Quản Lý Người Dùng</inertia-link>
                <inertia-link class="block px-6 py-2 hover:bg-indigo-500 hover:text-white w-full text-left" :href="route('logout')" method="delete" as="button">Đăng Xuất</inertia-link>
              </div>
            </dropdown>
          </div>
        </div>
        <div class="md:flex md:flex-grow md:overflow-hidden">
          <main-menu class="hidden md:block bg-indigo-800 flex-shrink-0 w-64 p-6 overflow-y-auto" />
          <div class="md:flex-1 px-4 py-8 md:py-10 px-1 md:overflow-y-auto md:h-full" scroll-region>
            <flash-messages />
            <slot />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/Shared/Icon'
import Logo from '@/Shared/Logo'
import Dropdown from '@/Shared/Dropdown'
import MainMenu from '@/Shared/MainMenu'
import FlashMessages from '@/Shared/FlashMessages'

export default {
  components: {
    Dropdown,
    FlashMessages,
    Icon,
    Logo,
    MainMenu,
  },
}
</script>
