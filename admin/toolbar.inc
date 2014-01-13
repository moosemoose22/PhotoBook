	<div id="toolbar" style="position:absolute; z-index:10000; border: 1px solid black">
		<table cellspacing="0" cellpadding="0">
		<tr><td style="font-size: 20px; font-weight: bold; text-align: center">Toolbar</td></tr>
		<tr><td>Mouse mode tools</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Pointer" id="Arrow" src="<?=$g_system_icon_web_location?>mouse_mode/pointer.jpg" /></td>
				<td><img title="Text tool" id="ExitText" src="<?=$g_system_icon_web_location?>mouse_mode/text_tool.jpg" /></td>
				<td><img title="Draw rectangle" src="<?=$g_system_icon_web_location?>mouse_mode/draw_rectangle.jpg" /></td>
				<td><img title="Draw circle" src="<?=$g_system_icon_web_location?>mouse_mode/draw_circle.jpg" /></td>
				<td><img title="Eye dropper tool" src="<?=$g_system_icon_web_location?>mouse_mode/eye_dropper.jpg" /></td>
				<td><img title="Zoom in" src="<?=$g_system_icon_web_location?>mouse_mode/magnifying_glass.jpg" /></td>
				<td><img title="Pencil took" src="<?=$g_system_icon_web_location?>mouse_mode/pencil_freeform.jpg" /></td>
				<td><img title="Line tool" src="<?=$g_system_icon_web_location?>mouse_mode/pencil_line.jpg" /></td>
				<td><img title="Hand tool" src="<?=$g_system_icon_web_location?>mouse_mode/hand_tool.jpg" /></td>
			</tr>
			</table>
		</td></tr>
		<tr><td>Paragraph tools</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Align left" src="<?=$g_system_icon_web_location?>paragraph/text_align_left.jpg" /></td>
				<td><img title="Align center" src="<?=$g_system_icon_web_location?>paragraph/text_align_center.jpg" /></td>
				<td><img title="Align right" src="<?=$g_system_icon_web_location?>paragraph/text_align_right.jpg" /></td>
				<td><img title="Justify center" src="<?=$g_system_icon_web_location?>paragraph/text_align_justify.jpg" /></td>
				<td><img title="Indent first line of paragraph" src="<?=$g_system_icon_web_location?>paragraph/text_indent.jpg" /></td>
				<td><img title="Change font size" src="<?=$g_system_icon_web_location?>paragraph/font_size.jpg" /></td>
			</tr>
			</table>
		</td></tr>
		<tr><td>Modify object tools</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Rotate" src="<?=$g_system_icon_web_location?>modify_object/rotate.jpg" /></td>
				<td><img title="Change fill color" src="<?=$g_system_icon_web_location?>modify_object/color.jpg" /></td>
				<td><img title="Change border color" src="<?=$g_system_icon_web_location?>modify_object/border_color.jpg" /></td>
			</tr>
			</table>
		</td></tr>
		<tr><td>Book tools</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Add book" src="<?=$g_system_icon_web_location?>book/add_book.jpg" /></td>
				<td><img title="Add language" src="<?=$g_system_icon_web_location?>book/add_language.jpg" /></td>
				<td><img title="Publish book" src="<?=$g_system_icon_web_location?>book/publish_book.jpg" /></td>
			</tr>
			</table>
		</td></tr>
		<tr><td>Article tools</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Upload shared article" src="<?=$g_system_icon_web_location?>articles/add_document.jpg" /></td>
				<td><img title="Translate book" src="<?=$g_system_icon_web_location?>articles/translate.jpg" /></td>
				<!--<td><img title="Show uploaded shared articles" src="<?=$g_system_icon_web_location?>articles/show_uploaded_articles.jpg" /></td>-->
			</tr>
			</table>
		</td></tr>
		<tr><td>Photo tools</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Constrain photo to original dimensions" onclick="changeImageBehavior('fit');" src="<?=$g_system_icon_web_location?>photos/constrain_photo.jpg" /></td>
				<td><img title="Expand photo to fill to border" onclick="changeImageBehavior('fill');" src="<?=$g_system_icon_web_location?>photos/expand_photo.jpg" /></td>
				<td><img title="Don't wrap text around photo" src="<?=$g_system_icon_web_location?>photos/nowrap2.jpg" /></td>
				<td><img title="Wrap text around photo" src="<?=$g_system_icon_web_location?>photos/wrap2.jpg" /></td>
				<td><img title="Upload new photo" onclick="uploadFile(g_photoObjectType)" src="<?=$g_system_icon_web_location?>photos/upload_photo.jpg" /></td>
			</tr>
			</table>
		</td></tr>
		<tr><td>Logs</td></tr>
		<tr><td>
			<table cellspacing="0" cellpadding="0" class="icons">
			<tr>
				<td><img title="Log from server" onclick="Logger.showLog('from_server')" src="<?=$g_system_icon_web_location?>logs/from_server.jpg" /></td>
				<td><img title="Log to server" onclick="Logger.showLog('to_server')" src="<?=$g_system_icon_web_location?>logs/to_server.jpg" /></td>
			</tr>
			</table>
		</td></tr>
<!--		
		<tr>
		<td><img title="Text mode" src="<?=$g_system_image_web_location?>Edit_Text_Icon_48.png" id="ExitText" />
		<img title="Mouse mode" src="<?=$g_system_image_web_location?>System_mouse_Icon_48_down.png" id="Arrow" />
		</td><td><img title="Stretch image to fill" onclick="changeImageBehavior('fill');" src="<?=$g_system_image_web_location?>System_Photo_StretchToFill.gif" id="StretchImage" />
		<img title="Keep original image proportions" onclick="changeImageBehavior('fit');" src="<?=$g_system_image_web_location?>System_Photo_FitInBorder.gif" id="FitImage" />
		</td><td><img title="Publish book!" src="<?=$g_system_image_web_location?>System_the_scream.jpg" id="GenerateBook" />
		</td></tr>
-->
		</table>
	</div>