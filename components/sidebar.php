
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="home.php">
        <div class="sidebar-brand-icon">
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="sidebar-brand-text mx-3"><?= isset($_SESSION["user_role"]) ? htmlspecialchars($_SESSION["user_role"]) : 'Guest' ?></div>
    </a>
    <hr class="sidebar-divider my-0" />
    <li class="nav-item <?= ($pageTitle ?? '') == "Dashboard" ? "active" : "" ?>">
        <a class="nav-link" href="home.php">
            <i class="bi bi-house<?= ($pageTitle ?? '') == "Dashboard" ? "-fill" : "" ?>"></i>
            <span>Dashboard</span></a>
    </li>
    <?php $myCasesPages = ["New Cases", "On-going Cases", "Solved Cases", "Reopened Cases"]; ?>
    <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "User"): ?>
        <li class="nav-item <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "active" : "" ?>">
            <a class="nav-link <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "" : "collapsed" ?>" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="<?= in_array(($pageTitle ?? ''), $myCasesPages) ? "true" : "false" ?>"
                aria-controls="collapseTwo">
                <i class="bi bi-briefcase<?= in_array(($pageTitle ?? ''), $myCasesPages) ? "-fill" : "" ?>"></i>
                <span>My Cases</span>
            </a>
            <div id="collapseTwo" class="collapse <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "show" : "" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "New Cases" ? "bg-primary text-white" : "" ?>" href="new_cases.php">New</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "On-going Cases" ? "bg-primary text-white" : "" ?>" href="ongoing_cases.php">On-going</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Solved Cases" ? "bg-primary text-white" : "" ?>" href="solved_cases.php">Solved</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Reopened Cases" ? "bg-primary text-white" : "" ?>" href="reopened_cases.php">Reopened</a>
                </div>
            </div>
        </li>
        <li class="nav-item <?= ($pageTitle ?? '') == "Purchased Products" ? "active" : "" ?>">
            <a class="nav-link" href="my_purchased_products.php">
                <i class="bi bi-bag<?= ($pageTitle ?? '') == "Purchased Products" ? "-fill" : "" ?>"></i>
                <span>Purchased Products</span>
            </a>
        </li>
    <?php elseif (isset($_SESSION["user_role"]) && ($_SESSION["user_role"] === "Engineer")): ?>
        <li class="nav-item <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "active" : "" ?>">
            <a class="nav-link <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "" : "collapsed" ?>" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="<?= in_array(($pageTitle ?? ''), $myCasesPages) ? "true" : "false" ?>"
                aria-controls="collapseTwo">
                <i class="bi bi-briefcase<?= in_array(($pageTitle ?? ''), $myCasesPages) ? "-fill" : "" ?>"></i>
                <span>My Cases</span>
            </a>
            <div id="collapseTwo" class="collapse <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "show" : "" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "New Cases" ? "bg-primary text-white" : "" ?>" href="new_cases.php">New</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "On-going Cases" ? "bg-primary text-white" : "" ?>" href="ongoing_cases.php">On-going</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Solved Cases" ? "bg-primary text-white" : "" ?>" href="solved_cases.php">Solved</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Reopened Cases" ? "bg-primary text-white" : "" ?>" href="reopened_cases.php">Reopened</a>
                </div>
            </div>
        </li>
        <li class="nav-item <?= ($pageTitle ?? '') == "Reports" ? "active" : "" ?>">
            <a class="nav-link" href="reports_table.php">
                <i class="bi bi-clipboard-data<?= ($pageTitle ?? '') == "Reports" ? "-fill" : "" ?>"></i>
                <span>Reports</span></a>
        </li>
    <?php elseif (isset($_SESSION["user_role"]) && ($_SESSION["user_role"] === "Technical Head" || $_SESSION["user_role"] === "Administrator")): ?>
        <?php $myCasesPages = ["New Cases", "On-going Cases", "Solved Cases", "Reopened Cases"]; ?>
        <?php $managementPages = ["My Cases", "Products", "Reports", "Purchased Products", "Engineers", "Users", "Settings"]; ?>
        <?php $purchasedProductsPages = ["All Purchased Products", "Add Purchased Product", "Active Licenses", "Expired Licenses"]; ?>

        <li class="nav-item <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "active" : "" ?>">
            <a class="nav-link <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "" : "collapsed" ?>" href="#" data-toggle="collapse" data-target="#myCasesCollapse" aria-expanded="<?= in_array(($pageTitle ?? ''), $myCasesPages) ? "true" : "false" ?>"
                aria-controls="myCasesCollapse">
                <i class="bi bi-briefcase<?= in_array(($pageTitle ?? ''), $myCasesPages) ? "-fill" : "" ?>"></i>
                <span>My Cases</span>
            </a>
            <div id="myCasesCollapse" class="collapse <?= in_array(($pageTitle ?? ''), $myCasesPages) ? "show" : "" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "New Cases" ? "bg-primary text-white" : "" ?>" href="new_cases.php">New</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "On-going Cases" ? "bg-primary text-white" : "" ?>" href="ongoing_cases.php">On-going</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Solved Cases" ? "bg-primary text-white" : "" ?>" href="solved_cases.php">Solved</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Reopened Cases" ? "bg-primary text-white" : "" ?>" href="reopened_cases.php">Reopened</a>
                </div>
            </div>
        </li>
        <li class="nav-item <?= ($pageTitle ?? '') == "Products" ? "active" : "" ?>">
            <a class="nav-link" href="products.php">
                <i class="bi bi-terminal<?= ($pageTitle ?? '') == "Products" ? "-fill" : "" ?>"></i>
                <span>Products</span></a>
        </li>
        <li class="nav-item <?= ($pageTitle ?? '') == "Reports" ? "active" : "" ?>">
            <a class="nav-link" href="reports_table.php">
                <i class="bi bi-clipboard-data<?= ($pageTitle ?? '') == "Reports" ? "-fill" : "" ?>"></i>
                <span>Reports</span></a>
        </li>
        <li class="nav-item <?= in_array(($pageTitle ?? ''), $purchasedProductsPages) ? "active" : "" ?>">
            <a class="nav-link <?= in_array(($pageTitle ?? ''), $purchasedProductsPages) ? "" : "collapsed" ?>" href="#" data-toggle="collapse" data-target="#purchasedProductsCollapse" aria-expanded="<?= in_array(($pageTitle ?? ''), $purchasedProductsPages) ? "true" : "false" ?>"
                aria-controls="purchasedProductsCollapse">
                <i class="bi bi-bag<?= in_array(($pageTitle ?? ''), $purchasedProductsPages) ? "-fill" : "" ?>"></i>
                <span>Purchased Products</span>
            </a>
            <div id="purchasedProductsCollapse" class="collapse <?= in_array(($pageTitle ?? ''), $purchasedProductsPages) ? "show" : "" ?>" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "All Purchased Products" ? "bg-primary text-white" : "" ?>" href="all_purchased_products.php">All Purchased Products</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Active Licenses" ? "bg-primary text-white" : "" ?>" href="active_licenses.php">License Lists</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Companies" ? "bg-primary text-white" : "" ?>" href="all_companies.php">Companies</a>
                </div>
            </div>
        </li>
    <?php endif; ?>
    <?php if (isset($_SESSION["user_role"])): ?>
        <li class="nav-item <?= ($pageTitle ?? '') == "Emails" ? "active" : "" ?>">
            <a class="nav-link" href="all_notifications.php">
                <i class="bi bi-envelope<?= ($pageTitle ?? '') == "Emails" ? "-fill" : "" ?>"></i>
                <span>Emails</span>
            </a>
        </li>
    <?php endif; ?>
    <hr class="sidebar-divider" />
    <div class="sidebar-heading">Options</div>
    <?php $usersPages = ["Engineers", "Users"]; ?>
    <?php if (isset($_SESSION["user_role"]) && ($_SESSION["user_role"] === "Administrator" || $_SESSION["user_role"] === "Technical Head")): ?>
        <li class="nav-item <?= in_array(($pageTitle ?? ''), $usersPages) ? "active" : "" ?>">
            <a class="nav-link <?= in_array(($pageTitle ?? ''), $usersPages) ? "" : "collapsed" ?>" href="#" data-toggle="collapse" data-target="#usersCollapse" aria-expanded="<?= in_array(($pageTitle ?? ''), $usersPages) ? "true" : "false" ?>"
                aria-controls="usersCollapse">
                <i class="bi bi-people<?= in_array(($pageTitle ?? ''), $usersPages) ? "-fill" : "" ?>"></i>
                <span>Users</span>
            </a>
            <div id="usersCollapse" class="collapse <?= in_array(($pageTitle ?? ''), $usersPages) ? "show" : "" ?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Menu:</h6>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Engineers" ? "bg-primary text-white" : "" ?>" href="engineers.php">Engineers</a>
                    <a class="collapse-item <?= ($pageTitle ?? '') == "Users" ? "bg-primary text-white" : "" ?>" href="users.php">Users</a>
                </div>
            </div>
        </li>
    <?php endif; ?>
    <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "Administrator"): ?>
        <li class="nav-item <?= ($pageTitle ?? '') == "Settings" ? "active" : "" ?>">
            <a class="nav-link" href="settings.php">
                <i class="bi bi-gear<?= ($pageTitle ?? '') == "Settings" ? "-fill" : "" ?>"></i>
                <span>Settings</span></a>
        </li>
         
    <?php endif; ?>
    <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "User"): ?>
        <li class="nav-item <?= ($pageTitle ?? '') == "Knowledge Base" ? "active" : "" ?>">
            <a class="nav-link" href="knowledge_base.php">
                <i class="bi bi-database<?= ($pageTitle ?? '') == "Knowledge Base" ? "-fill" : "" ?>"></i>
                <span>Knowledge Base</span></a>
        </li>
    <?php endif; ?>
    <li class="nav-item <?= ($pageTitle ?? '') == "About" ? "active" : "" ?>">
        <a class="nav-link" href="about.php">
            <i class="bi bi-exclamation-circle<?= ($pageTitle ?? '') == "About" ? "-fill" : "" ?>"></i>
            <span>About</span></a>
    </li>
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>