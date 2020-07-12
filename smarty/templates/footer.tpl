

{if $user}
    <hr/>
<div class="uk-container uk-container-small uk-container-center uk-text-center">

    <div class="uk-width-1-1@l">
        {$user.name} -
        {if $permissions.admin}{if $admin_limited_view}<a href="/?admin">Full view</a>{else}<a href="/">CP</a>{/if} |{/if}
        <a href="settings.php">Settings</a> |
        {if $permissions.admin}<a href="queue.php">Queue</a> |{/if}
        {if $permissions.admin}<a href="admin_users.php">Users</a> |{/if}
        <a href="logout.php">Log out</a>
    </div>
</div>
{/if}

</body>
</html>