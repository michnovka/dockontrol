{include file="header.tpl" title="Dock Z9 Control"}


<div class="uk-container uk-container-large uk-container-center uk-text-center">

    <h2 class="uk-heading-line uk-margin-small uk-margin-small-top uk-text-center"><span>{if !$edit_user.id}Add new user{else}Edit user{/if}</span></h2>

    {if $error_message}
        <div class="uk-alert-danger uk-text-center uk-margin-medium" uk-alert>
            <p>{$error_message}</p>
        </div>
    {/if}

    {if $success}
        <div class="uk-alert-success uk-text-center uk-margin-medium" uk-alert>
            <p>{$success}</p>
        </div>
    {/if}

    <form class="uk-form-stacked uk-margin-large uk-text-left" method="post">

        <input type="hidden" name="id" value="{$edit_user.id}">

        <div class="uk-margin">
            <label class="uk-form-label" for="username">Username</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.username}uk-form-danger{/if}" id="username" type="text" placeholder="Username" value="{$edit_user.username}" name="username">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="password">Password</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.password}uk-form-danger{/if}" id="password" type="password" placeholder="Enter to change" value="" name="password">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="name">Name</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.name}uk-form-danger{/if}" id="name" type="text" placeholder="Name" value="{$edit_user.name}" name="name">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="email">Email</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.email}uk-form-danger{/if}" id="email" type="email" placeholder="E-mail" value="{$edit_user.email}" name="email">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="phone">Phone</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.phone}uk-form-danger{/if}" id="phone" type="tel" placeholder="Phone" value="{$edit_user.phone}" name="phone">
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="apartment">Apartment</label>
            <div class="uk-form-controls">
                <input class="uk-input {if $error.phone}uk-form-danger{/if}" id="apartment" type="text" placeholder="ZX.BY.NNN" value="{$edit_user.apartment}" name="apartment">
            </div>
        </div>


        <div class="uk-margin">
            <label class="uk-form-label" for="default_garage">Default garage</label>
            <div class="uk-form-controls">
                <select class="uk-select" id="default_garage" name="default_garage">
                    <option value="z7"{if $edit_user.default_garage == 'z7'} selected{/if}>Z7</option>
                    <option value="z8"{if $edit_user.default_garage == 'z8'} selected{/if}>Z8</option>
                    <option value="z9"{if $edit_user.default_garage == 'z9'} selected{/if}>Z9</option>
                </select>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="enabled">Enabled</label>
            <div class="uk-form-controls">
                <input class="uk-checkbox" id="enabled" name="enabled" type="checkbox" value="1"{if $edit_user.enabled} checked{/if}>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="has_camera_access">Camera access</label>
            <div class="uk-form-controls">
                <input class="uk-checkbox" id="has_camera_access" name="has_camera_access" type="checkbox" value="1"{if $edit_user.has_camera_access} checked{/if}>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="can_create_guests">Can create guest passes</label>
            <div class="uk-form-controls">
                <input class="uk-checkbox" id="can_create_guests" name="can_create_guests" type="checkbox" value="1"{if $edit_user.can_create_guests} checked{/if}>
            </div>
        </div>


        <div class="uk-margin">
            <div class="uk-form-label">Groups</div>
            <div class="uk-form-controls uk-form-controls-text">
                {section name=g loop=$groups}
                <label><input class="uk-checkbox" type="checkbox" name="groups[]" value="{$groups[g].id}" {if array_search($groups[g].id,$user_groups) !== false} checked{/if}> {$groups[g].name}</label><br>
                {/section}
            </div>
        </div>

        <div class="uk-margin">
            <div class="uk-form-controls">
                <button type="submit" name="action" value="save" class="uk-button uk-button-primary uk-button-large uk-width-full">Save</button>
            </div>
        </div>
        {if $edit_user.id}
        <div class="uk-margin-small">
            <div class="uk-form-controls">
                <button type="submit" name="action" value="delete" class="uk-button uk-button-danger uk-button-large uk-width-full">DELETE ACCOUNT</button>
            </div>
        </div>

        {/if}
    </form>



</div>


{include file="footer.tpl"}
