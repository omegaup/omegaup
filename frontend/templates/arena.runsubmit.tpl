			<form id="submit" method="POST">
				<button class="close">&times;</button>
				<div id="lang-select">
					{#wordsLanguage#}
					<select name="language">
						<option value="" default="default"></option>
						<option value="cpp11">C++11</option>
						<option value="cpp">C++</option>
						<option value="c">C</option>
						<option value="hs">Haskell</option>
						<option value="java">Java</option>
						<option value="pas">Pascal</option>
						<option value="py">Python</option>
						<option value="rb">Ruby</option>
						<option value="kp">Karel (Pascal)</option>
						<option value="kj">Karel (Java)</option>
						<option value="cat">{#wordsJustOutput#}</option>
					</select>
				</div>
				<div>{#arenaRunSubmitFilename#} <tt>Main<span id="submit-filename-extension"></span></tt></div>
				<label for="submit-code-contents">{#arenaRunSubmitPaste#}</label>
				<textarea name="code" id="submit-code-contents"></textarea><br/>
				<label for="submit-code-file">{#arenaRunSubmitUpload#}</label>
				<input type="file" id="submit-code-file" /><br/>
				<input type="submit" />
			</form>

