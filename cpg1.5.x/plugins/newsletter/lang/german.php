<?php
/**************************************************
  Coppermine 1.5.x Plugin - newsletter
  *************************************************
  Copyright (c) 2009-2010 Joachim Müller
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ********************************************
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
  **************************************************/

$lang_plugin_newsletter['config_name'] = 'Newsletter';
$lang_plugin_newsletter['config_description'] = 'Ermöglicht Benutzern das abonnieren von Newslettern. Ermöglicht Administratoren das versenden von Newslettern..'; 
$lang_plugin_newsletter['author'] = 'Geschrieben von %s';
$lang_plugin_newsletter['drop_table_warning'] = 'Das Löschen von Tabellen kann nicht rückgängig gemacht werden (und genau das bewirken die o.g. Optionen)! Wenn Du Dir nicht sicher bist, dann aktiviere die oben genannten Optionen nicht.';
$lang_plugin_newsletter['remove_plugin_warning'] = 'Warnung: die Newsletter, die Du versendet hast beinhalten Links zu den Seiten, die durch dieses Plugin generiert werden. Das Entfernen des Plugins wird in broken Links für die Empfänger vorangegangener Mailings resultieren, was natürlich nicht gut aussieht.';
$lang_plugin_newsletter['config'] = 'Newsletter Einstellungen';
$lang_plugin_newsletter['subscribe_to_newsletter'] = 'Newsletter abonnieren';
$lang_plugin_newsletter['remove_config'] = 'Einstellungen löschen?';
$lang_plugin_newsletter['remove_config_explanation'] = 'Dies wird die Einstellungen des Newsletter-Plugins unwiederbringlich löschen.';
$lang_plugin_newsletter['subscriber_list'] = 'Liste der Abonennten';
$lang_plugin_newsletter['remove_subscribers'] = 'Abonnenten löschen?';
$lang_plugin_newsletter['remove_subscribers_explanation'] = 'Diese Aktion wird alle Einträge über die Abonennten der Liste löschen und damit die Liste leeren. Diese Aktion hat keine Auswirkung auf die Benutzertabelle der Galerie.';
$lang_plugin_newsletter['newsletter_categories'] = 'Newsletter Kategorien';
$lang_plugin_newsletter['remove_categories'] = 'Newsletter-Kategorien löschen?';
$lang_plugin_newsletter['remove_categories_explanation'] = 'Diese Aktion wird unwiederbringlich alle Newsletter-Kategorien löschen.';
$lang_plugin_newsletter['mailings'] = 'Mailings';
$lang_plugin_newsletter['remove_mailings'] = 'Liste der bisherigen Mailings löschen?';
$lang_plugin_newsletter['remove_mailings_explanation'] = 'Diese Aktion wird die Liste aller Newsletter-Mailings löschen, die Du jemals versendet hast. Deine Abonnenten werden davon nichts mitbekommen.';
$lang_plugin_newsletter['mail_queue'] = 'Mail-Warteschlange';
$lang_plugin_newsletter['remove_queue'] = 'Das Versenden der ausstehenden Newsletter-Mailings abbrechen und die Mail-Warteschlange löschen?';
$lang_plugin_newsletter['remove_queue_explanation'] = 'In der Warteschlange sind noch Mails vorhanden, die geplant sind versendet zu werden. Willst Du wirklich das Versenden abbrechen und den Inhalt der Warteschlange unwiederbringlich löschen?';
$lang_plugin_newsletter['announcement_thread'] = 'Ankündigungs-Thread';
$lang_plugin_newsletter['allow_guest_subscriptions'] = 'Abonnements durch Gäste erlauben?';
$lang_plugin_newsletter['salutation_for_guests'] = 'Anrede für Gäste';
$lang_plugin_newsletter['from_email'] = 'Email-Adresse, die verwendet wird, um Newsletter zu verschicken';
$lang_plugin_newsletter['from_name'] = 'Name, der verwendet wird, um Newsletter zu verschicken';
$lang_plugin_newsletter['mails_per_page'] = 'Mails pro Seite';
$lang_plugin_newsletter['administration_links'] = 'Newsletter Admin-Links im Admin-Menü anzeigen?';
$lang_plugin_newsletter['display_newsletter_in_menu_for_visitor'] = 'Newsletter im Menü für normale Besucher anzeigen?';
$lang_plugin_newsletter['in_sys_menu'] = 'im Sys-Menu';
$lang_plugin_newsletter['in_sub_menu'] = 'im Sub-Menu';
$lang_plugin_newsletter['link_to_newsletter_index_page'] = 'Link zur Übersichtsseite für Newsletter';
$lang_plugin_newsletter['several_links_control'] = 'mehrere Links zu jeder einzelnen Newsletter-Kontrollseite';
$lang_plugin_newsletter['changes_saved'] = 'Deine Änderungen wurden gespeichert';
$lang_plugin_newsletter['no_changes'] = 'Es gab keine Änderungen oder Deine Eingaben waren ungültig';
$lang_plugin_newsletter['submit_to_install'] = 'Sende dieses Formular ab zum Installieren des Plugins';
$lang_plugin_newsletter['newsletter-subscription'] = 'Newsletter abonnieren';
$lang_plugin_newsletter['welcome_x'] = 'Willkommen %s,';
$lang_plugin_newsletter['your_name'] = 'Dein Name';
$lang_plugin_newsletter['your_email'] = 'Deine Email-Adresse';
$lang_plugin_newsletter['not_allowed_to_subscribe'] = 'Du darfst nicht abonnieren.';
$lang_plugin_newsletter['alternatively_register_to subscribe'] = 'Als Alternative zum Abonnieren des Newsletters mit Deiner eMail-Adresse (als Gast) könntest Du Dir einen Benutzernamen %sregistrieren%s, um dann einfach als registrierter Benutzer Newsletter abonnieren zu können.';
$lang_plugin_newsletter['register_to subscribe'] = 'Um einen Newsletter zu abonnieren musst Du Dich %sregistrieren%s, um einen Benutzerkonto zu erhalten. Du kannst anschließend Newsletter abonnieren.';
$lang_plugin_newsletter['pluginmgr_lnk'] = 'Plugin Manager';
$lang_plugin_newsletter['email_address_invalid'] = 'Ungültige Email-Adresse';
$lang_plugin_newsletter['your_cant_edit_this_field'] = 'Du kannst den Inhalt dieses Feldes hier nicht bearbeiten, da er aus Deinem Profil übernommen wurde. Bearbiet stattdessen Dein Profil, um den Inhalt dieses Feldes zu ändern.';
$lang_plugin_newsletter['email_from_your_profile'] = 'Email-Adresse wurde aus Deinem %sProfil%s übernommen.';
$lang_plugin_newsletter['email_from_profile'] = 'mail-Adresse wurde aus dem %sProfil%s übernommen.';
$lang_plugin_newsletter['category_list'] = 'Newsletter Kategorieliste';
$lang_plugin_newsletter['frequency_once_year'] = 'Einmal jährlich';
$lang_plugin_newsletter['frequency_twice_year'] = 'Zweimal im Jahr';
$lang_plugin_newsletter['frequency_once_month'] = 'Monatlich';
$lang_plugin_newsletter['frequency_twice_month'] = 'Zweimal im Monat';
$lang_plugin_newsletter['frequency_once_week'] = 'Wöchentlich';
$lang_plugin_newsletter['frequency_once_day'] = 'Täglich';
$lang_plugin_newsletter['frequency_x_times_per_year'] = '%s mal im Jahr';
$lang_plugin_newsletter['delete'] = 'Löschen';
$lang_plugin_newsletter['name'] = 'Name';
$lang_plugin_newsletter['email_address'] = 'Email-Adresse';
$lang_plugin_newsletter['description'] = 'Beschreibung';
$lang_plugin_newsletter['open'] = 'Offen';
$lang_plugin_newsletter['open_explanation'] = 'Offen für Abonnements, d.h. Besucher können diese Kategorie abonnieren.';
$lang_plugin_newsletter['closed'] = 'Geschlossen';
$lang_plugin_newsletter['closed_explanation'] = 'Geschlossen für Abonnements, d.h. Besucher können diese Kategorie nicht abonnieren.';
$lang_plugin_newsletter['viewable'] = 'Sichtbar';
$lang_plugin_newsletter['viewable_explanation'] = 'Archive können öffentlich angesehen werden, d.h. Besucher können vergangene Mailings aus dieser Kategegorie ansehen, bevor sie die Kategorie abonnieren.';
$lang_plugin_newsletter['not_viewable'] = 'Nicht sichtbar';
$lang_plugin_newsletter['not_viewable_explanation'] = 'Archive können nicht öffentlich angesehen werden, d.h. Besucher können vergangene Mailings aus dieser Kategegorie nicht ansehen, bevor sie die Kategorie abonnieren.';
$lang_plugin_newsletter['frequency'] = 'Frequenz';
$lang_plugin_newsletter['frequency_explanation'] = 'Legt die maximale Anzahl der Mailings pro Zeitraum fest. Diese Angabe wird während des Abonnement-Vorgangs angezeigt. Es ist daher ratsam, sich daran zu halten, da die Abonnenten sonst ungehalten reagieren könnten, wenn Du mehr als die angegebene Anzahl von Mailings versenden solltest..';
$lang_plugin_newsletter['add'] = 'Hinzufügen';
$lang_plugin_newsletter['changes_saved_for_category_x'] = 'Änderungen für Kategorie %s gespeichert';
$lang_plugin_newsletter['category_x_created'] = 'Kategorie %s erzeugt';
$lang_plugin_newsletter['category_x_deleted'] = 'Kategorie %s gelöscht';
$lang_plugin_newsletter['mailings'] = 'Mailings';
$lang_plugin_newsletter['subscriptions'] = 'Abonnements';
$lang_plugin_newsletter['subscribe_to'] = 'Abonniere';
$lang_plugin_newsletter['subscribe'] = 'Abonnieren';
$lang_plugin_newsletter['no_newsletter_to_subscribe_to'] = 'Es gibt derzeit keine Newsletter, die Du abonnieren könntest.';
$lang_plugin_newsletter['browse_archived_mailings'] = 'In archivierten Mailings stöbern';
$lang_plugin_newsletter['archive'] = 'Newsletter Archiv';
$lang_plugin_newsletter['subscription_updated'] = 'Abonnement wurde aktualisert';
$lang_plugin_newsletter['no_update_needed'] = 'Keine Aktualiserung notwendig';
$lang_plugin_newsletter['successfully_subscribed'] = 'Du hast den Abonnements-Vorgang erfolgreich abgeschlossen';
$lang_plugin_newsletter['you_have_unsubscribed'] = 'Du hast das Newsletter-Abonnement erfolgreich beendet';
$lang_plugin_newsletter['at_least_one_category_needed'] = 'Du musst mindestens eine Kategorie abonnieren um Mailings zu erhalten';
$lang_plugin_newsletter['edit_your_subscription'] = 'Dein Abonnement bearbeiten';
$lang_plugin_newsletter['edit_x_subscription'] = 'Abonnement von %s bearbeiten';
$lang_plugin_newsletter['no_email_in_profile'] = 'In Deinem Profil ist keine gültige Email-Adresse hinterlegt. Newsletter Mailings werden ohne Email-Adresse nicht funktionieren.';
$lang_plugin_newsletter['outdated_link'] = 'Abgelaufener Link';
$lang_plugin_newsletter['outdated_link_explain'] = 'Leider ist der Link, dem Du gefolgt bist nicht länger gültig. Die Seite, die ursprünglich hier zu sehen war wurde deaktiviert, d.h. Newsletter Mailings wurden eingestellt. Falls Du hierher gekommen bist um Dein Newsletter-Abonnement zu kündigen brauchst Du Dir keine Sorgen zu machen - es gibt nichts mehr, das Du kündigen könntest. Du wurdest auf die Index-Seite der Galerie weitergeleitet.';
$lang_plugin_newsletter['create_mailing'] = 'Erzeuge Mailing';
$lang_plugin_newsletter['salutation'] = 'Anrede';
$lang_plugin_newsletter['default_salutation'] = 'Hallo {USERNAME},';
$lang_plugin_newsletter['salutation_explain'] = 'Benutze {USERNAME} als Platzhalter für den tatsächlichen Benutzernamen.';
$lang_plugin_newsletter['subject'] = 'Betreff';
$lang_plugin_newsletter['default_subject'] = '%s Neuigkeiten über %s';
$lang_plugin_newsletter['body'] = 'Text';
$lang_plugin_newsletter['default_body'] = 'You\'re receiving this email because you have subscribed to it at %s. Thanks for reading.';
$lang_plugin_newsletter['unsubscribe_text'] = 'To unsubscribe from future mailings, click on the following link:';
$lang_plugin_newsletter['unsubscribe_html'] = 'Unsubscribe from future mailings';
$lang_plugin_newsletter['category'] = 'Category';
$lang_plugin_newsletter['one_subscription'] = '1 subscription';
$lang_plugin_newsletter['x_subscriptions'] = '%s subscriptions';
$lang_plugin_newsletter['one_mailing'] = '1 mailing';
$lang_plugin_newsletter['x_mailings'] = '%s mailings';
$lang_plugin_newsletter['you_need_to_select_a_category'] = 'You need to select a category!';
$lang_plugin_newsletter['you_need_at_least_one_category'] = 'You need at least one category!';
$lang_plugin_newsletter['create_category'] = 'Create category';
$lang_plugin_newsletter['several_ways_to_browse_archive'] = 'There are several ways to browse the archive.';
$lang_plugin_newsletter['choose_method'] = 'Choose a method:';
$lang_plugin_newsletter['browse_by_category'] = 'Browse by category';
$lang_plugin_newsletter['browse_by_date'] = 'Browse by date';
$lang_plugin_newsletter['search_the_archive'] = 'Search the archive';
$lang_plugin_newsletter['date_sent'] = 'Date sent';
$lang_plugin_newsletter['recipients'] = 'Recipients';
$lang_plugin_newsletter['mailing_created'] = 'Mailing created';
$lang_plugin_newsletter['sending_email_failed'] = 'Sending mailing %s to %s (attempt %s) failed';
$lang_plugin_newsletter['sending_email_succeeded'] = 'Sending mailing %s to %s succeeded';
$lang_plugin_newsletter['send_mailings'] = 'Send mailings';
$lang_plugin_newsletter['send_mailings_explain'] = 'On this page, the mailings are actually being sent. The number of emails sent simultaneously depends on what you have set up for "mails per page" on the plugin\'s %sconfig screen%s. Alternatively, you can leave this page completely and do something else. Each time your gallery get\'s visited, %s will be processed every %s from the mailing queue.';
$lang_plugin_newsletter['remaining_queued_mailings'] = 'Remaining queued mailings: %s';
$lang_plugin_newsletter['remaining_time'] = 'Remaining time: %s';
$lang_plugin_newsletter['all_queued_mailings_processed'] = 'All queued mailings have been processed.';
$lang_plugin_newsletter['page_refresh_delay'] = 'Page refresh delay';
$lang_plugin_newsletter['seconds'] = 'seconds';
$lang_plugin_newsletter['timed_out_mailings'] = 'Timed-out mailings: %s';
$lang_plugin_newsletter['one_email'] = 'one email';
$lang_plugin_newsletter['x_emails'] = '%s emails';
$lang_plugin_newsletter['second'] = 'second';
$lang_plugin_newsletter['x_seconds'] = '%s seconds';
$lang_plugin_newsletter['minute'] = 'minute';
$lang_plugin_newsletter['x_minutes'] = '%s minutes';
$lang_plugin_newsletter['category_delete_confirmation'] = 'Are you sure that you want to permanently delete this category? It\\\'s recommended to just close categories instead of deleting them!'; // JS-Alert
$lang_plugin_newsletter['subscription_doesnt_exist'] = 'Subscription ID %s doesn\'t exist.';
$lang_plugin_newsletter['admin_subscription_edit_warning'] = 'You\'re about to edit the subscription of one of your visitors. It\'s advisable to strictly stick to the opt-in principle: don\'t force your visitors to subscribe to something without their consent. Only enable subscriptions on behalf of a user if he asked you to do so.';
$lang_plugin_newsletter['queue'] = 'Queue';
$lang_plugin_newsletter['no_broken_mailings'] = 'There are currently no broken mailings inside the queue.';
$lang_plugin_newsletter['re_send'] = 'Re-send';
$lang_plugin_newsletter['page_will_refresh_in_x'] = 'Page will refresh in %s';
$lang_plugin_newsletter['reloading'] = 'Reloading';
$lang_plugin_newsletter['retries'] = 'Retries';
$lang_plugin_newsletter['retries_explain'] = 'Number of times the script will attempt to re-send an email if sending fails before timing out';
$lang_plugin_newsletter['x_open_mailings'] = '%s newsletter mailings need to be processed.';
$lang_plugin_newsletter['submit_to_uninstall'] = 'The plugin isn\'t uninstalled yet. Submit the form to actually uninstall the plugin.';
$lang_plugin_newsletter['menu'] = 'Menu';
$lang_plugin_newsletter['statistics'] = 'Statistics';
$lang_plugin_newsletter['email_address_belongs_to_registered_subscriber'] = 'The email address you entered belongs to a registered user. If it\'s actually your email address, %slog in%s with your user name and password and then return to this screen to manage your subscription.';
$lang_plugin_newsletter['subscription_exists_for_email_address'] = 'There already is a subscription record for the email address %s. Use another email address if you want to start a fresh newsletter subscription. To edit your existing subscription or to unsubscribe, click %shere%s.';
$lang_plugin_newsletter['mail_sent'] = 'An email has been sent to your account. Please check your inbox.';
$lang_plugin_newsletter['newsletter_subscription_authentification'] = 'Newsletter subscription authentification';
$lang_plugin_newsletter['click_to_edit_subscription'] = 'To edit your subscription, click on the following link:';
$lang_plugin_newsletter['default_on_register'] = 'Per default subscribe to all newsletters on registration';
$lang_plugin_newsletter['opt_in'] = 'opt-in';
$lang_plugin_newsletter['opt_out'] = 'opt-out';
$lang_plugin_newsletter['recommended'] = 'recommended';
$lang_plugin_newsletter['not_recommended'] = 'not recommended';
?>