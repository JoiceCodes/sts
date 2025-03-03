<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <div class="sidebar-brand-icon">
            <!-- <i class="fas fa-laugh-wink"></i> -->
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Engineer</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0" />

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="home.php">
            <i class="bi bi-house"></i>
            <span>Home</span></a>
    </li>

    <!-- Heading -->
    <!-- <div class="sidebar-heading">Cases Overview</div> -->

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="bi bi-briefcase"></i>
            <span>My Cases</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Menu:</h6>
                <a class="collapse-item" href="new_cases.php">New</a>
                <a class="collapse-item" href="ongoing_cases.php">On-going</a>
                <a class="collapse-item" href="solved_cases.php">Solved</a>
                <a class="collapse-item" href="reopened_cases.php">Reopened</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider" />

    <!-- Heading -->
    <div class="sidebar-heading">Options</div>

    <li class="nav-item">
        <a class="nav-link" href="all_purchased_products.php">
            <i class="bi bi-bag"></i>
            <span>All Purchased Products</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="about.php">
            <i class="bi bi-exclamation-circle"></i>
            <span>About</span></a>
    </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>