{include file="header.tpl" title="Users CP"}


<div class="uk-container uk-container-expand uk-container-center uk-text-center">

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
                <th>Last login time</th>
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
                    <td>{$users[u].last_login_time}</td>
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

    {if $signup_key_changed}
        <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
            <p>Key changed successfully. Old one will no longer work.</p>
        </div>
    {/if}

   Give this URL to users to sign up:
    <div class="uk-margin">
        <input class="uk-input uk-form-width-large" type="text" value="{$signup_url}" readonly>
    </div>
    <div class="uk-margin">
        <img src="qrcode.php?content={$signup_url|urlencode}" srcset="qrcode.php?content={$signup_url|urlencode}&size=5,qrcode.php?content={$signup_url|urlencode}&size=10 2x" />
    </div>
    <div class="uk-margin">
        <a class="uk-button uk-button-default uk-button-medium" href="admin_users.php?change_signup_key=1">Change key</a>
    </div>

</div>


{include file="footer.tpl"}
