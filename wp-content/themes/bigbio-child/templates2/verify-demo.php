<?php /* Template Name: Verify-Demo */ ?>

<?php get_header(); ?>

		<script>
			function next1(){
				document.getElementById('first').style.display = 'none';
				document.getElementById('second').style.display = 'inline';
				document.getElementById('third').style.display = 'none';
			}
			function next2(){
				document.getElementById('registration-header').style.display = "none";
				document.getElementById('first').style.display = 'none';
				document.getElementById('second').style.display = 'none';
				document.getElementById('third').style.display = 'inline';
			}
			function prev2(){
				document.getElementById('first').style.display = 'inline';
				document.getElementById('second').style.display = 'none';
				document.getElementById('third').style.display = 'none';
			}
		</script>


		<div class="mm_container"> 
		<div id="registration-header"> 
			<h4>Registration</h4>
		</div>

		<?php wp_nonce_field('verify_one', 'security'); ?>
		<input type="hidden" id="vkey" value="<?php echo $vkey; ?>"> 
		</input>
		<div class="status_error"><li >This is where error messages are displayed</li></div>
			<div id="first">

				<div class="form-group">
					<label for="fname">First Name</label>
					<input type="text" id="fname" class="form-control" placeholder="First Name" maxlength="30">
				</div>
				<div class="form-group">
					<label for="lname">Last Name</label>
					<input type="text" id="lname" class="form-control" placeholder="Last Name" maxlength="30">
				</div>
				<div class="form-group">
					<label for="username">Username</label>
					<input type="text" id="username" class="form-control" placeholder="username" maxlength="20">
				</div>
				<div class="form-group">
					<label for="pwd">Password</label>
					<input type="password" id="pwd" class="form-control" placeholder="Password">
				</div>
				<div class="form-group">
					<label for="pwd2">Repeat Password</label>
					<input type="password" id="pwd2" class="form-control" placeholder="Password">
				</div>
				<div class="form-group">
					<button class="btn btn-danger" onclick="next1()" id="next-1">Next</button>
				</div>
			</div>

			<div id="second">

				<div class="form-group">
					<label>(1 - No knowledge, 10 - Very knowledgeable)</label>
					<form oninput="x.value = parseInt(rank_bio.value)">
					<label>How knowledgeable are you in Biology?</label>
					<input id="rank_bio" type="range" min="0" max="10" step="1" >
					<output name="x" for="rank_bio">5</output>
					</form>

					<form oninput="y.value = parseInt(rank_stats.value)">
					<label>How knowledgeable are you in Statistics?</label>
					<input id="rank_stats" type="range" min="0" max="10" step="1" >
					<output name="y" for="rank_stats">5</output>
					</form>

					<form oninput="z.value = parseInt(rank_cs.value)">
					<label>How knowledgeable are you in Computer Science?</label>
					<input id="rank_cs" type="range" min="0" max="10" step="1" >
					<output name="z" for="rank_cs">5</output>
					</form>
				</div>


				<div class="form-group">
					<label>Ocupation</label>
					<select id="ocupation">
						<option value="k12student">K-12 student</option>
						<option value="undergraduate">Undergraduate student</option>
						<option value="graduate">Graduate student</option>
						<option value="postdoc">Postdoc</option>
						<option value="uni_faculty">Univeristy faculty</option>
						<option value="k12teacher">K-12 teacher</option>
						<option value="industry_sci">Industry Scientist</option>
						<option value="adminstaff">Administrative Staff</option>
						<option value="other">Other</option>
					</select>
				</div>

				<div class="form-group">
					<label>Employer</label>
					<select id="employer">
					</select>
				</div>


				<div class="form-group">
					<label>Primary Field</label>
					<select id="primary_field">
						<option value="neuroscience">Neuroscience</option>
						<option value="biology">Biology</option>
						<option value="bioinformatics">Bioinformatics</option>
						<option value="genetics">Genetics</option>
						<option value="cs">Computer Science</option>
						<option value="stats">Statistics</option>
						<option value="medicine">Medicine</option>
						<option value="math">Mathematics</option>
						<option value="chem">Chemistry</option>
						<option value="humanities">Humanities</option>
						<option value="media">Media</option>
						<option value="business">Business</option>
						<option value="pharma">Pharmaceuticals</option>
						<option value="other">Other</option>
					</select>
				</div>


				<div class="form-group">
					<label>Gender</label>
					<select id= "gender" name="gender">
						<option value="female">Female</option>
						<option value="male">Male</option>
						<option value="notsay">Prefer not to say</option>
					</select>
				</div>
				<div class="form-group">
					<label> What is your race or ethnicity? Select all that apply. </label>
					<div id="ethn-box">
						Asian: <input type="checkbox" name="ethnicity[]" value="Asian"><br>
						Black/African: <input type="checkbox" name="ethnicity[]" value="Black/African"><br>
						Caucasian: <input type="checkbox" name="ethnicity[]" value="Caucasian"><br>
						Hispanic/Latinx: <input type="checkbox" name="ethnicity[]" value="Hispanic/Latinx"><br>
						Pacific Islander: <input type="checkbox" name="ethnicity[]" value="Pacific Islander"><br>
						Middle Eastern: <input type="checkbox" name="ethnicity[]" value="Middle Eastern"><br>
						Other: <input id="eth-other" type="text" name="ethnicity" maxlength="50"><br>
					</div>
				</div>
				<div class="form-group">
					<label for="country-box">Country</label>
					<div id="country-box">
						<select id= "country">
						</select>
					</div>
				</div>




				<div class="form-group">
					<label for="school-box">Are you part of UCLA?</label>
					<div id="school-box">
						Yes: <input type="checkbox" name="school[]" value="UCLA"><br>
						Other: <input id="school-other" type="text" name="school[]"><br>
					</div>
				</div>
				<div class="form-group">
					<label for="reason-box">Why are you signing up for Big Bio?</label>
					<div id="reason-box">
						As a part of class: <input type="checkbox" name="reason[]" value="part of class"> <br>
						Preparing to do research in this area (e.g. as a undergraduate mentee or entering graduate student): <input type="checkbox" name="reason[]" value="prepare to do research"> <br>
						General Knowledge: <input type="checkbox" name="reason[]" value="general knowledge"> <br>
						As a part of a program (e.g. a summer research program): <input type="checkbox" name="reason[]" value="part of program"> <br>
						Other: <input id="reason-other" type="text" name="reason[]" maxlength="50">
					</div>
				</div>
				<div class="form-group">
					<label for="edu-box">Which of the following best desribes you?</label>
					<div id="edu-box">
						Undergraduate: <input type="checkbox" name="edu[]" value="undergraduate"> <br>
						Graduate Student: <input type="checkbox" name="edu[]" value="graduate student"> <br>
						Postdoc: <input type="checkbox" name="edu[]" value="postdoc"> <br>
						Faculty: <input type="checkbox" name="edu[]" value="faculty"> <br>
						Other: <input id="edu-other" type="text" name="edu[]" maxlength="50">
					</div>
				</div>
				<div class="form-group">
					<button onclick="prev2()" class="btn btn-danger" id="prev-2">Previous</button>
				</div>
				<div class="form-group">
					<button onclick ="next2()" class="btn btn-danger" id="next-2">Next</button>
				</div>
			</div>
		</div>


<script>
	document.getElementById('first').style.display = 'none';
	document.getElementById('second').style.display = 'inline';
	document.getElementById('third').style.display = 'none';
</script>

<?php get_footer(); ?>

