

{if $user}
    <hr/>

<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <div class="uk-width-1-1@l">
        {if $guest}
            Guest of {$user.name} | Expires: {$guest.expires}{if $guest.remaining_actions > 0} | Remaining actions: {$guest.remaining_actions}{/if}
        {else}

            {$user.name} -
            {if $permissions.admin && $admin_limited_view}<a href="/?admin">Full view</a>{else}<a href="/">CP</a>{/if} |
            <a href="settings.php">Settings</a> |
            {if $user.can_create_guests}<a href="create_guest.php">Guest pass</a> |{/if}
            <a href="logout.php">Log out</a><br/>
            <strong>ADMIN - </strong>
            {if $permissions.super_admin}<a href="admin_queue.php">Queue</a> |{/if}
            {if $permissions.admin}<a href="admin_users.php">Users</a> |{/if}
            {if $permissions.super_admin}<a href="admin_stats.php">Stats</a> |{/if}
            {if $permissions.super_admin}<a href="admin_groups.php">Groups</a> |{/if}
            {if $permissions.super_admin}<a href="admin_crons.php">CRONs</a> |{/if}
            {if $permissions.super_admin}<a href="admin_monitor_nodes.php">Nodes</a>{/if}


        {/if}
    </div>
</div>
{/if}

</body>
</html>