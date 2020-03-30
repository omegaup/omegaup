			<form id="submit" method="POST">
				<button class="close">&times;</button>
				<div>
					{#wordsLanguage#}
					<select name="language">
						<option value="" default="default"></option>
						<option value="cpp17-gcc">C++17 (g++ 7.4)</option>
						<option value="cpp17-clang">C++17 (clang++ 6.0)</option>
						<option value="cpp11-gcc">C++11 (g++ 7.4)</option>
						<option value="cpp11-clang">C++11 (clang++ 6.0)</option>
						<option value="c11-gcc">C (gcc 7.4)</option>
						<option value="c11-clang">C (clang 6.0)</option>
						<option value="cs">C# (dotnet 2.2)</option>
						<option value="hs">Haskell (ghc 8.0)</option>
						<option value="java">Java (openjdk 11.0)</option>
						<option value="pas">Pascal (fpc 3.0)</option>
						<option value="py3">Python 3.6</option>
						<option value="py2">Python 2.7</option>
						<option value="rb">Ruby (2.5)</option>
						<option value="lua">Lua (5.2)</option>
						<option value="kp">Karel (Pascal)</option>
						<option value="kj">Karel (Java)</option>
						<option value="cat">{#wordsJustOutput#}</option>
					</select>
				</div>
				<div>{#arenaRunSubmitFilename#} <tt>Main<span class="submit-filename-extension"></span></tt></div>
				<label for="editor">{#arenaRunSubmitPaste#}</label>
				<textarea id="editor" name="code"></textarea><br/>
				<label>{#arenaRunSubmitUpload#} <input type="file" /></label><br/>
				<input type="submit" />
				{if !empty($payload)}
					<script type="text/json" id="payload">{$payload|json_encode}</script>
				{/if}
			</form>
