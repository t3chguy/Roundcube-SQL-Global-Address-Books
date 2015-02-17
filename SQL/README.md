#Custom SQL View Guidelines and Tips#

The View has to be in the following layout:
+ ID has to be a Unique Integer but does not have to be ordered or anything else, just unique and static, must not change per user [but can change if the user changes, so can be based on the timestamp of user modification/creation]
+ name is any string that will be displayed as the name, it will also be searched in when running searches both in autocomplete and the addressbook search module.
+ email is a comma seperated list [alias support] of e-mail addresses, the first e-mail is the primary and will be preferred by Roundcube but others will be shown too.
+ domain is used for DomainBooks but is required also for the Whitelist and Blacklist so the column must exist, it allows Distrubution Lists to work properly within groups of the DomainBook in Roundcube also.

| ID       | name                      | email                          | domain           |
|---------:|---------------------------|--------------------------------|------------------|
| 21981839 | First Middle Last         | foo@bar.com                    | bar.com          |
| 1        | Michael Daniel Telatynski | postmaster@webdevguru.co.uk    | webdevguru.co.uk |
| 2        | John Smith                | john@smith.com,owner@smith.com | smith.com        |

If you have any questions about how to create a custom view then just drop me a message, or use one of the included views as a base. If you create a view you think others could benefit from please Pull Request this repo and add it to the /SQL/ folder.