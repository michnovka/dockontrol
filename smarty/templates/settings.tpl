{include file="header.tpl" title="Settings"}

<div class="uk-container uk-container-medium uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>Settings</span></h2>

    {if $error}
    <div class="uk-alert-danger uk-text-center uk-margin-medium" uk-alert>
        <p>{$error}</p>
    </div>
    {/if}

    {if $success}
    <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
        <p>{$success}</p>
    </div>
    {/if}


    <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>
        <div class="uk-width-1-1"><h4 class="uk-h4">My info</h4></div>

        <div class="uk-width-1-3@l"><strong>Username:</strong> {$user.username}</div>
        <div class="uk-width-1-3@l"><strong>Default object:</strong> {$user.default_garage|upper}</div>
        <div class="uk-width-1-3@l"><strong>Created:</strong> {$user.created}</div>

    </div>


    <form action="settings.php" method="post">
        <div class="uk-grid-small uk-text-center uk-grid-row-small uk-margin-top" uk-grid>
            <div class="uk-width-1-1"><h4 class="uk-h4">Change password</h4></div>

            <div class="uk-width-1-3@l"><input type="password" class="uk-input uk-width-1-1" value="" placeholder="New password" name="password"></div>
            <div class="uk-width-1-3@l"><input type="password" class="uk-input uk-width-1-1" value="" placeholder="Repeat password" name="password2"></div>
            <div class="uk-width-1-3@l"><button name="action" type="submit" class="uk-button uk-button-primary uk-width-1-1" value="change_password">Save</button></div>

        </div>
    </form>

    <form action="settings.php" method="post">
        <div class="uk-grid-small uk-text-center uk-grid-row-small uk-margin-top" uk-grid>
            <div class="uk-width-1-1"><h4 class="uk-h4">Change contacts</h4></div>

            <div class="uk-width-1-3@l"><input type="tel" class="uk-input uk-width-1-1" value="{$user.phone}" placeholder="Phone" name="phone" /></div>
            <div class="uk-width-1-3@l"><input type="email" class="uk-input uk-width-1-1" value="{$user.email}" placeholder="Email" name="email"></div>
            <div class="uk-width-1-3@l"><input type="name" class="uk-input uk-width-1-1" value="{$user.name}" placeholder="Name" name="name"></div>
            <div class="uk-width-1-1"><button name="action" type="submit" class="uk-button uk-button-primary uk-width-1-1" value="change_contacts">Save</button></div>

        </div>
    </form>

</div>

<div class="uk-container uk-container-medium uk-container-center uk-text-center uk-padding-large">

    <h3 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>NUKI</span></h3>
    <p>You can add NUKI devices using <a href="https://github.com/michnovka/dockontrol-nuki-api">dockontrol-nuki-api</a> bridge to your CP.</p>
    <p>Security is achieved through 2 passwords - one password is stored on the server and second password is saved in browser local cache. The passwords are used to generate TOTP codes, therefore not even the provider of the server is able to issue lock/unlock commands to your NUKI. Intercepting communication (which should be over HTTPS anyways) would only allow attacker to only unlock/lock door during the same 1 minute interval.</p>
    <p>In order to protect against stolen mobile devices, NUKI commands can be further protected with PIN code. This code will be required with every lock/unlock command and is rate-limited.</p>

    <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nuki Name</th>
                <th>API URL</th>
                <th>Username</th>
                <th>Password1</th>
                <th>Password2</th>
                <th>PIN</th>
                <th>Can lock?</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {section name=n loop=$nukis}
            <tr>
                <td>#{$nukis[n].id}</td>
                <td>{$nukis[n].name}</td>
                <td>{$nukis[n].dockontrol_nuki_api_server}</td>
                <td>{$nukis[n].username}</td>
                <td>{if $nukis[n].password1}SET{else}NOT SET{/if}</td>
                <td><script type="text/javascript">if(localStorage.getItem('nuki_{$nukis[n].id}_password')) document.write('SET'); else document.write('NOT SET');</script></td>
                <td>{if $nukis[n].pin === null}NOT SET{else}SET{/if}</td>
                <td>{if $nukis[n].can_lock}YES{else}NO{/if}</td>
                <td><a class="uk-button uk-button-default uk-button-small" href="nuki_edit.php?id={$nukis[n].id}" title="Edit {$nukis[n].name}">Edit</a></td>
            </tr>
            {sectionelse}
                <tr>
                    <td colspan="8">No NUKIs at the moment</td>
                </tr>
            {/section}
        </tbody>
    </table>

    <a class="uk-button uk-button-primary uk-width-1-1" href="nuki_edit.php">Add new NUKI</a>

</div>


{include file="footer.tpl"}
