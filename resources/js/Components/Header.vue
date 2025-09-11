<template>
  <header class="c-header c-header-light c-header-fixed c-header-with-subheader">
    <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar"
      data-class="c-sidebar-show">
      <svg xmlns="http://www.w3.org/2000/svg" width="1.6em" height="1.6em" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
      </svg>
    </button>
    <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar"
      data-class="c-sidebar-lg-show" responsive="true">
      <svg xmlns="http://www.w3.org/2000/svg" width="1.6em" height="1.6em" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
      </svg>
    </button>
    <ul class="c-header-nav ml-auto mr-4">

      <li class="c-header-nav-item dropdown">
        <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button"
          aria-haspopup="true" aria-expanded="false">
          <!-- Avatar dengan dropdown trigger -->
          <div class="c-avatar">
            <img class="c-avatar-img"
              :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=4e73df&color=ffffff&size=100`">
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right pt-0 mb-0 pb-0">
          <!-- Link Profile yang benar -->
          <Link href="/apps/profile" class="dropdown-item" role="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
              <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
            </svg>
            <span class="ms-2">Profile</span>
          </Link>




          <div class="dropdown-divider"></div>

          <!-- User Info -->
          <div class="dropdown-item-text">
            <div class="d-flex align-items-center">
              <div class="c-avatar c-avatar-sm me-2">
                <img class="c-avatar-img"
                  :src="`https://ui-avatars.com/api/?name=${$page.props.auth.user.name}&background=4e73df&color=ffffff&size=40`">
              </div>
              <div>
                <div class="fw-bold">{{ $page.props.auth.user.name }}</div>
                <small class="text-muted">{{ $page.props.auth.user.email }}</small>
              </div>
            </div>
          </div>

          <div class="dropdown-divider"></div>

          <!-- Logout -->
          <Link href="/logout" method="POST" as="button" class="dropdown-item text-danger" role="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
              <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
            </svg>
            <span class="ms-2">Logout</span>
          </Link>
        </div>
      </li>
    </ul>
  </header>
</template>

<script>
  //import Link
  import { Link } from '@inertiajs/inertia-vue3';

  export default {
    components: {
      Link
    }
  }
</script>

<style scoped>
  /* Avatar hover effect */
  .c-avatar {
    cursor: pointer;
    transition: transform 0.2s ease;
  }

  .c-avatar:hover {
    transform: scale(1.05);
  }

  /* Dropdown styling */
  .dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    min-width: 250px;
  }

  .dropdown-item {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    transition: background-color 0.2s ease;
  }

  .dropdown-item:hover {
    background-color: #f8f9fa;
  }

  .dropdown-item svg {
    margin-right: 8px;
  }

  .dropdown-item-text {
    padding: 12px 16px;
  }

  .dropdown-divider {
    margin: 8px 0;
  }

  /* Small avatar in user info */
  .c-avatar-sm {
    width: 40px;
    height: 40px;
  }

  .c-avatar-sm .c-avatar-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }

  /* Text danger for logout */
  .text-danger:hover {
    background-color: #f8d7da !important;
  }
</style>
