[![Build Status](https://travis-ci.com/DiscipleTools/disciple-tools-starter-plugin-template.svg?branch=master)](https://travis-ci.com/DiscipleTools/disciple-tools-starter-plugin-template)

# Disciple Tools - ManyChat Integration
This is a "proof of concept" project to create an integration between Disciple Tools and ManyChat. The purpose is to get a 
basic contact creation integration in place. Please, feel free to fork the project and take it further. 

This plugin will connect ManyChat to Disciple Tools in three ways:
1. An External Request Action in ManyChat can create a contact record in Disciple Tools from the ManyChat subscriber
and return to ManyChat the post_id of the Disciple Tools contact and store it in a custom field on the ManyChat subscriber record.
1. An External Request Action in ManyChat can send a message to Disciple Tools to log a comment to a contact record. (i.e. "subscriber
accomplished action X" or "subscriber was sent X campaign")
1. The plugin adds a button to a contact record which links back to a ManyChat's live chat interface. 

Obviously, there are a number of other integrations that would be interesting and helpful, but for this version 1 effort
these are the two functions supported. Again, feel free to fork the project and improve on it.

## Create Contact Setup
<ol>
    <li>
        Create a "ManyChat" Site-to-site Link. <a href="<?php echo esc_url( admin_url() ) . '/post-new.php?post_type=site_link_system'  ?>">Create new link</a>
        <ol style="list-style-type: lower-alpha;">
            <li>Give the link any title you want.</li>
            <li>Add site #1 as "manychat"</li>
            <li>Add site #2 as the current site. (Hint: Use the auto fill link)</li>
            <li>Set the type to "ManyChat".</li>
        </ol>
    </li>
    <li>
        Make sure you have configuration information on the "Configuration" Tab. <a href="<?php echo esc_url( admin_url() ) . 'admin.php?page=dt_manychat&tab=general'  ?>">Configuration Tab</a>
    </li>
    <li>
        In ManyChat create an "Action" in one of your workflows. This action needs to be an "External Request".
        <ol style="list-style-type: lower-alpha;">
            <li>Add External Request Action step to a "Flow"</li>
            <li>Open External Request Action dialogue box.</li>
            <li>Transfer the connection information from the "Configuration" Tab under the heading "For Creating A New Record" to the fields in the External Request dialogue box.
            <ol style="list-style-type: lower-roman;">
                <li>Set Request Type to POST</li>
                <li>Copy URL to Request URL box.</li>
                <li>Add to "Header" section two key/value fields: token: {provided value from configuration tab}, and action: "create" </li>
                <li>Add to "Body" section the pre-defined "Add Full Subscriber Data"</li>
                <li>Add to "Response mapping" section, JSONPath: '$.post_id', Select Custom Field: 'dt_post_id'. Note: add the custom field 'dt_post_id' if you haven't already.</li>
            </ol>
            </li>
        </ol>
    </li>
    <li>
        Test connection. You should see a new contact created in Disciple Tools. You will also get a response of 200/success.
    </li>
</ol>



## Log Message Setup
<ol>
    <li>Make sure you have gone through the setup steps above.</li>
    <li>
        In ManyChat create an "Action" in one of your workflows. This action needs to be an "External Request".
        <ol style="list-style-type: lower-alpha;">
            <li>Add External Request Action step to a "Flow"</li>
            <li>Open External Request Action dialogue box.</li>
            <li>Transfer the connection information from the "Configuration" Tab under the heading "For Logging Comments" to the fields in the External Request dialogue box.</li>
            <ol style="list-style-type: lower-roman;">
                <li>Set Request Type to POST</li>
                <li>Copy URL to Request URL box.</li>
                <li>Add to "Header" section two key/value fields: token: {provided value from configuration tab}, and action: "comment" </li>
                <li>Add to "Body" section the pre-defined string provided in the configuration body section. This is a JSON string and must be copied exactly.<br>
                    <ol style="list-style-type: lower-alpha;">
                        <li>"post_id" = (int) This is the Contact record id from Disciple Tools that was saved during the create record process. You can also add this to a record directly through their contact page in Manychat.</li>
                        <li>Note: <code>dt_post_id</code> is the live variable added from the custom field drop down. This custom field must be created before it will show up in the drop down.</li>
                        <li>"message" = (string) This can be any string of any length. It will be logged into the comments area of the contact record.</li>
                        <li>"skip_notification" = (bool) This is either set to true or false and it controls whether the contact owner in Disciple Tools gets a notification that the comment was added. True means "do not notify", False means notify.</li>
                    </ol>
                </li>
            </ol>
        </ol>
    </li>
    <li>
        Test connection. You should see a new contact created in Disciple Tools. You will also get a response of 200/success.
    </li>
</ol>

---
###### Button on Contact in Disciple Tools linking back to Live Chat (if available)

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/live-chat-button.png "Live Chat Button")
---
###### ManyChat Action Example

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/mc-external-request-action.png "External Request Action Selection")



___
## Setup for Create Contact
###### ManyChat Edit Request Screen -- Create Contact - Header Tab Example

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/mc-edit-headers-create.png "Edit Headers Section for Create")
---
###### ManyChat Edit Request Screen - Create Contact - Body Tab Example

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/mc-edit-body-create.png "Edit Body Section for Create")
---
###### ManyChat Edit Request Screen - Body Tab Example

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/mc-edit-responsemapping-create.png "Response Mapping Screen")



___
## Setup for Comment
###### ManyChat Edit Request Screen - Body Tab Example

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/mc-edit-headers-comment.png "Edit Headers Section for Comments")
---
###### ManyChat Edit Request Screen - Body Tab Example

![alt text](https://raw.githubusercontent.com/DiscipleTools/disciple-tools-manychat/master/images/mc-edit-body-comment.png "Edit Body Section for Comments")
---




