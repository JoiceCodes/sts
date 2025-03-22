<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="home.php">
        <div class="sidebar-brand-icon">
            <!-- <i class="fas fa-laugh-wink"></i> -->
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="sidebar-brand-text mx-3"><?= $_SESSION["user_role"] ?></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0" />

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= $pageTitle == "Home" ? "active" : "" ?>">
        <a class="nav-link" href="home.php">
            <i class="bi bi-house<?= $pageTitle == "Home" ? "-fill" : "" ?>"></i>
            <span>Home</span></a>
    </li>

    <!-- Heading -->
    <!-- <div class="sidebar-heading">Cases Overview</div> -->
    <?php $myCasesPages = ["New Cases", "On-going Cases", "Solved Cases", "Reopened Cases"]; ?>
    <?php if ($_SESSION["user_role"] === "User"): ?>
        <!-- <li class="nav-item <?= $pageTitle == "My Cases" ? "active" : "" ?>">
            <a class="nav-link" href="my_cases.php">
                <i class="bi bi-briefcase<?= $pageTitle == "My Cases" ? "-fill" : "" ?>"></i>
                <span>My Cases</span></a>
        </li> -->
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item <?= !in_array($pageTitle, $myCasesPages) ? "" : "active" ?>">
            <a class="nav-link <?= !in_array($pageTitle, $myCasesPages) ? "collapsed" : "" ?>" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="<?= !in_array($pageTitle, $myCasesPages) ? "false" : "true" ?>"
                aria-controls="collapseTwo">
                <i class="bi bi-briefcase<?= !in_array($pageTitle, $myCasesPages) ? "" : "-fill" ?>"></i>
                <span>My Cases</span>
            </a>
            <div id="collapseTwo" class="collapse <?= !in_array($pageTitle, $myCasesPages) ? "" : "show" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= $pageTitle == "New Cases" ? "bg-primary text-white" : ""  ?>" href="new_cases.php">New</a>
                    <a class="collapse-item <?= $pageTitle == "On-going Cases" ? "bg-primary text-white" : ""  ?>" href="ongoing_cases.php">On-going</a>
                    <a class="collapse-item <?= $pageTitle == "Solved Cases" ? "bg-primary text-white" : ""  ?>" href="solved_cases.php">Solved</a>
                    <a class="collapse-item <?= $pageTitle == "Reopened Cases" ? "bg-primary text-white" : ""  ?>" href="reopened_cases.php">Reopened</a>
                </div>
            </div>
        </li>
    <?php elseif ($_SESSION["user_role"] === "Engineer"): ?>
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item <?= !in_array($pageTitle, $myCasesPages) ? "" : "active" ?>">
            <a class="nav-link <?= !in_array($pageTitle, $myCasesPages) ? "collapsed" : "" ?>" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="<?= !in_array($pageTitle, $myCasesPages) ? "false" : "true" ?>"
                aria-controls="collapseTwo">
                <i class="bi bi-briefcase<?= !in_array($pageTitle, $myCasesPages) ? "" : "-fill" ?>"></i>
                <span>My Cases</span>
            </a>
            <div id="collapseTwo" class="collapse <?= !in_array($pageTitle, $myCasesPages) ? "" : "show" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= $pageTitle == "New Cases" ? "bg-primary text-white" : ""  ?>" href="new_cases.php">New</a>
                    <a class="collapse-item <?= $pageTitle == "On-going Cases" ? "bg-primary text-white" : ""  ?>" href="ongoing_cases.php">On-going</a>
                    <a class="collapse-item <?= $pageTitle == "Solved Cases" ? "bg-primary text-white" : ""  ?>" href="solved_cases.php">Solved</a>
                    <a class="collapse-item <?= $pageTitle == "Reopened Cases" ? "bg-primary text-white" : ""  ?>" href="reopened_cases.php">Reopened</a>
                </div>
            </div>
        </li>
    <?php elseif ($_SESSION["user_role"] === "Technical Engineer" || $_SESSION["user_role"] === "Technical Head"): ?>
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item <?= !in_array($pageTitle, $myCasesPages) ? "" : "active" ?>">
            <a class="nav-link <?= !in_array($pageTitle, $myCasesPages) ? "collapsed" : "" ?>" href="#" data-toggle="collapse" data-target="#myCasesCollapse" aria-expanded="<?= !in_array($pageTitle, $myCasesPages) ? "false" : "true" ?>"
                aria-controls="myCasesCollapse">
                <i class="bi bi-briefcase<?= !in_array($pageTitle, $myCasesPages) ? "" : "-fill" ?>"></i>
                <span>My Cases</span>
            </a>
            <div id="myCasesCollapse" class="collapse <?= !in_array($pageTitle, $myCasesPages) ? "" : "show" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= $pageTitle == "New Cases" ? "bg-primary text-white" : ""  ?>" href="new_cases.php">New</a>
                    <a class="collapse-item <?= $pageTitle == "On-going Cases" ? "bg-primary text-white" : ""  ?>" href="ongoing_cases.php">On-going</a>
                    <a class="collapse-item <?= $pageTitle == "Solved Cases" ? "bg-primary text-white" : ""  ?>" href="solved_cases.php">Solved</a>
                    <a class="collapse-item <?= $pageTitle == "Reopened Cases" ? "bg-primary text-white" : ""  ?>" href="reopened_cases.php">Reopened</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <?php if ($_SESSION["user_role"] === "Technical Head"): ?>
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item <?= $pageTitle == "Products" ? "active" : "" ?>">
            <a class="nav-link" href="products.php">
                <i class="bi bi-terminal<?= $pageTitle == "Products" ? "-fill" : "" ?>"></i>
                <span>Products</span></a>
        </li>
        <li class="nav-item <?= $pageTitle == "Reports" ? "active" : "" ?>">
            <a class="nav-link" href="reports_table.php">
                <i class="bi bi-clipboard-data<?= $pageTitle == "Reports" ? "-fill" : "" ?>"></i>
                <span>Reports</span></a>
        </li>
    <?php endif; ?>

    <!-- Divider -->
    <hr class="sidebar-divider" />

    <!-- Heading -->
    <div class="sidebar-heading">Options</div>

    <?php $usersPages = ["Engineers", "Users"]; ?>
    <?php if ($_SESSION["user_role"] === "Technical Head" || $_SESSION["user_role"] === "Technical Engineer"): ?>
        <!-- Nav Item - Pages Collapse Menu -->
        <li class="nav-item <?= !in_array($pageTitle, $usersPages) ? "" : "active" ?>">
            <a class="nav-link <?= !in_array($pageTitle, $usersPages) ? "collapsed" : "" ?>" href="#" data-toggle="collapse" data-target="#usersCollapse" aria-expanded="<?= !in_array($pageTitle, $usersPages) ? "false" : "true" ?>"
                aria-controls="usersCollapse">
                <i class="bi bi-people<?= !in_array($pageTitle, $usersPages) ? "" : "-fill" ?>"></i>
                <span>Users</span>
            </a>
            <div id="usersCollapse" class="collapse <?= !in_array($pageTitle, $usersPages) ? "" : "show" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= $pageTitle == "Engineers" ? "bg-primary text-white" : ""  ?>" href="engineers.php">Engineers</a>
                    <a class="collapse-item <?= $pageTitle == "Users" ? "bg-primary text-white" : ""  ?>" href="users.php">Users</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <li class="nav-item <?= $pageTitle == "All Purchased Products" ? "active" : "" ?>">
        <a class="nav-link" href="all_purchased_products.php">
            <i class="bi bi-bag<?= $pageTitle == "All Purchased Products" ? "-fill" : "" ?>"></i>
            <span>All Purchased Products</span></a>
    </li>

    <?php if ($_SESSION["user_role"] === "User"): ?>
        <li class="nav-item <?= $pageTitle == "Knowledge Base" ? "active" : "" ?>">
            <a class="nav-link" href="knowledge_base.php">
                <i class="bi bi-database<?= $pageTitle == "Knowledge Base" ? "-fill" : "" ?>"></i>
                <span>Knowledge Base</span></a>
        </li>
    <?php endif; ?>

    <li class="nav-item <?= $pageTitle == "About" ? "active" : "" ?>">
        <a class="nav-link" href="about.php">
            <i class="bi bi-exclamation-circle<?= $pageTitle == "About" ? "-fill" : "" ?>"></i>
            <span>About</span></a>
    </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>