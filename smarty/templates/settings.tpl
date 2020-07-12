{include file="header.tpl" title="Settings"}

<div class="uk-container uk-container-small uk-container-center uk-text-center">

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


<div id="open_garage_z9_modal" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-margin-auto-vertical uk-padding-medium">
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">Open Garage</h2>
        </div>
        <div class="uk-modal-body">
            <div class="uk-alert-success uk-text-center uk-margin-medium notify_modal" style="display:none;" uk-alert>
                <p></p>
            </div>
            <div class="uk-grid-small uk-text-center uk-grid-row-small" uk-grid>
                <div class="uk-width-1-2@l"><button name="action" id="open_garage_z9" type="button" class="uk-button uk-button-large uk-button-primary uk-width-1-1 clickable clickable_modal" value="enter">SINGLE OPEN</button></div>
                <div class="uk-width-1-2@l"><button name="action" id="open_garage_z9_1min" type="button" class="uk-button uk-button-large uk-button-danger uk-width-1-1 clickable clickable_modal" value="exit">OPEN FOR 1 MIN</button></div>
            </div>
        </div>
    </div>
</div>


{include file="footer.tpl"}
