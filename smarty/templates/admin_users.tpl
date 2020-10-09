{include file="header.tpl" title="Users CP"}


<div class="uk-container uk-container-expand uk-container-center uk-text-center">
    {if $signup_key_error}
        <div class="uk-alert-danger uk-text-center uk-margin-medium" uk-alert>
            <p>Error creating signup key: {$signup_key_error}</p>
        </div>
    {elseif $signup_key_created}
        <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
            <p>New link created successfully. It expires on {$signup_key_expires}</p>
        </div>

        Give this URL to users to sign up:
        <div class="uk-margin">
            <input class="uk-input uk-form-width-large" type="text" value="{$signup_url}" readonly>
        </div>
        <div class="uk-margin">
            <img src="qrcode.php?content={$signup_url|urlencode}" srcset="qrcode.php?content={$signup_url|urlencode}&size=5,qrcode.php?content={$signup_url|urlencode}&size=10 2x" />
        </div>

    {/if}

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Users CP</span></h2>

    <table style="width:100%" class="uk-table uk-table-striped uk-table-hover uk-table-small">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Time created</th>
                <th>Enabled</th>
                <th>Groups</th>
                <th>Last command time</th>
                <th>Apartment</th>
                <th>Default garage</th>
                <th>E-mail</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            {section name=u loop=$users}
                <tr>
                    <td>{$users[u].id}</td>
                    <td>{$users[u].username}</td>
                    <td>{$users[u].name}</td>
                    <td>{$users[u].created}</td>
                    <td>{$users[u].enabled}</td>
                    <td>{$users[u].groups_names}</td>
                    <td>{if !$users[u].last_command_time}NEVER{else}{if $permissions.super_admin}<a href="admin_queue.php?user_id={$users[u].id}" title="View action history for {$users[u].username|escape}">{/if}{$users[u].last_command_time}{if $permissions.super_admin}</a>{/if}{/if}</td>
                    <td>{$users[u].apartment}</td>
                    <td>{$users[u].default_garage}</td>
                    <td>{$users[u].email}</td>
                    <td>{$users[u].phone}</td>
                    <td><a class="uk-button uk-button-default uk-button-small" href="admin_edit_user.php?id={$users[u].id}" title="Edit {$users[u].username}">Edit</a></td>
                </tr>
            {/section}
        </tbody>
    </table>

    <a class="uk-button uk-button-primary uk-width-1-1" href="admin_edit_user.php">Add new user</a>

    <h4 class="uk-heading-line uk-margin-small uk-margin-large-top uk-text-center" id="signup_url"><span>Signup URL</span></h4>


    <form action="admin_users.php" method="post">
        <div class="uk-margin">
            <label for="apartment_mask">Apartment mask</label>
            <input type="text" class="uk-input uk-width-medium" name="apartment_mask" id="apartment_mask" placeholder="ZX.BY or ZX.BY.NNN" />
        </div>
        <div class="uk-margin">
            <button type="submit" class="uk-button uk-button-default uk-button-medium" name="create_new_signup_key" value="1">Create new key</button>
        </div>
    </form>

</div>


{include file="footer.tpl"}
