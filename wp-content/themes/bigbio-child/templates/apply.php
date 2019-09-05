<?php /* Template Name: Apply */ ?>

<?php get_header(); check_logged()?> 
    <div class="mm_container">
    <form name="contributor">  
        <h1>Apply to be a Contributor</h1> 

        <section> 
            <label for="resume"><h5>Upload CV</h5></label> 
            <div id="files-cont"> <span> </span> </div>  
            <input class="input_file" id="resume" type="file" name="cv" onChange="add_file(event,this)"/>
            <label for="resume">choose file</label>
        </section> 

        <section> 
            <label for="degree"><h5>Highest Degree</h5></label> 
            <select id="degree">
                <option value="" selected="selected" disabled="disabled">-- select one --</option>
                <option value="No formal education">No formal education</option>
                <option value="Primary education">Primary education</option>
                <option value="Secondary education">Secondary education or high school</option>
                <option value="GED">GED</option>
                <option value="Vocational qualification">Vocational qualification</option>
                <option value="Bachelor's degree">Bachelor's degree</option>
                <option value="Master's degree">Master's degree</option>
                <option value="Doctorate or higher">Doctorate or higher</option>
            </select>
        </section> 

        <button class="submit_button" type="submit">Submit</button>
    </form> 
    </div> 

<?php get_footer();?>

<script src= "../wp-content/themes/bigbio-child/js/styling.js"> </script>
