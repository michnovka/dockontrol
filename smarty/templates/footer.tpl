

{if $user}
    <hr/>

<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <div class="uk-width-1-1@l">
        {if $guest}
            Guest of {$user.name} | Expires: {$guest.expires}{if $guest.remaining_actions > 0} | Remaining actions: {$guest.remaining_actions}{/if}<br/>
        {else}

            {$user.name} -
            {if $permissions.admin && $admin_limited_view}<a href="/?admin">Full view</a>{else}<a href="/">CP</a>{/if} |
            <a href="settings.php">Settings</a> |
            {if $user.can_create_guests}<a href="create_guest.php">Guest pass</a> |{/if}
            <a href="logout.php">Log out</a><br/>
            {if $permissions.admin || $permissions.super_admin}

                <strong>ADMIN - </strong>
                {if $permissions.super_admin}<a href="admin_queue.php">Queue</a> |{/if}
                {if $permissions.admin}<a href="admin_users.php">Users</a> |{/if}
                {if $permissions.admin}<a href="admin_cameras.php">Cameras</a> |{/if}
                {if $permissions.super_admin}<a href="admin_stats.php">Stats</a> |{/if}
                {if $permissions.super_admin}<a href="admin_groups.php">Groups</a> |{/if}
                {if $permissions.super_admin}<a href="admin_crons.php">CRONs</a> |{/if}
                {if $permissions.super_admin}<a href="admin_monitor_nodes.php">Nodes</a>{/if}
                <br/>
            {/if}
        {/if}
        v{$DOCKONTROL_VERSION} |
        <a href="https://github.com/michnovka/dockontrol" target="_blank" title="DOCKontrol github repo">
            <svg width="20" height="20" viewBox="0 0 1024 1024" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8C0 11.54 2.29 14.53 5.47 15.59C5.87 15.66 6.02 15.42 6.02 15.21C6.02 15.02 6.01 14.39 6.01 13.72C4 14.09 3.48 13.23 3.32 12.78C3.23 12.55 2.84 11.84 2.5 11.65C2.22 11.5 1.82 11.13 2.49 11.12C3.12 11.11 3.57 11.7 3.72 11.94C4.44 13.15 5.59 12.81 6.05 12.6C6.12 12.08 6.33 11.73 6.56 11.53C4.78 11.33 2.92 10.64 2.92 7.58C2.92 6.71 3.23 5.99 3.74 5.43C3.66 5.23 3.38 4.41 3.82 3.31C3.82 3.31 4.49 3.1 6.02 4.13C6.66 3.95 7.34 3.86 8.02 3.86C8.7 3.86 9.38 3.95 10.02 4.13C11.55 3.09 12.22 3.31 12.22 3.31C12.66 4.41 12.38 5.23 12.3 5.43C12.81 5.99 13.12 6.7 13.12 7.58C13.12 10.65 11.25 11.33 9.47 11.53C9.76 11.78 10.01 12.26 10.01 13.01C10.01 14.08 10 14.94 10 15.21C10 15.42 10.15 15.67 10.55 15.59C13.71 14.53 16 11.53 16 8C16 3.58 12.42 0 8 0Z" transform="scale(64)" fill="#1B1F23"/>
            </svg>
            GitHub repo
        </a>
        |
        <a href="/public/app-dockontrol-android.apk" title="Android app">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0 0 512 512">
                <path d="M387.512 378.89c0 10.701-8.632 19.385-19.353 19.385h-224.154c-10.701 0-19.364-8.683-19.364-19.385v-200.263c58.757-0.215 195.83-0.215 262.871 0v200.263z" fill="#000000" />
                <path d="M207.678 75.95c1.106 2.171 0.266 4.885-1.935 6.011v0c-2.181 1.106-4.894 0.236-6-1.945l-28.334-55.224c-1.126-2.201-0.226-4.864 1.956-6v0c2.181-1.127 4.874-0.256 6 1.936l28.314 55.224z" fill="#000000" />
                <path d="M310.876 78.275c-1.229 2.14-3.952 2.877-6.103 1.608v0c-2.13-1.209-2.857-3.932-1.608-6.073l31.027-53.73c1.229-2.13 3.973-2.887 6.082-1.628v0c2.151 1.218 2.877 3.953 1.649 6.072l-31.048 53.75z" fill="#000000" />
                <path d="M455.946 324.587c0 15.575-12.615 28.221-28.221 28.221v0c-15.606 0-28.252-12.636-28.252-28.221v-123.505c0-15.606 12.636-28.211 28.252-28.211v0c15.596 0 28.221 12.606 28.221 28.211v123.505z" fill="#000000" />
                <path d="M112.496 324.587c0 15.575-12.656 28.221-28.211 28.221v0c-15.616 0-28.242-12.636-28.242-28.221v-123.505c0-15.606 12.615-28.211 28.242-28.211v0c15.555 0 28.211 12.606 28.211 28.211v123.505z" fill="#000000" />
                <path d="M235.397 465.93c0 15.575-12.636 28.221-28.262 28.221v0c-15.575 0-28.19-12.636-28.19-28.221v-123.505c0-15.565 12.615-28.242 28.19-28.242v0c15.616 0 28.262 12.677 28.262 28.242v123.505z" fill="#000000" />
                <path d="M333.302 465.93c0 15.575-12.636 28.221-28.252 28.221v0c-15.575 0-28.221-12.636-28.221-28.221v-123.505c0-15.565 12.636-28.242 28.221-28.242v0c15.606 0 28.252 12.677 28.252 28.242v123.505z" fill="#000000" />
                <path d="M256.082 46.663c-68.649 0-125.184 52.245-132.741 119.399h265.472c-7.536-67.154-64.041-119.398-132.731-119.398zM196.188 119.050c-6.124 0-11.070-4.925-11.070-11.028 0-6.082 4.946-11.008 11.070-11.008 6.062 0 11.008 4.925 11.008 11.008 0 6.103-4.946 11.028-11.008 11.028zM316.211 119.050c-6.103 0-11.049-4.925-11.049-11.028 0-6.082 4.946-11.008 11.049-11.008 6.123 0 11.029 4.925 11.029 11.008 0 6.103-4.905 11.028-11.029 11.028z" fill="#000000" />
            </svg>
            Android app
        </a>
    </div>
</div>
{/if}

</body>
</html>