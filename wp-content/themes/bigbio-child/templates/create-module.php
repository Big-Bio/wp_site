<?php 
/* Template Name: create-module */
?>

<?php get_header(); check_logged();?>

<div class="mm_container creator_content">
	<h1>Create Module</h1>
  	<div id="status" class="status_error"></div>
  	<div class="create_module_container">

	    <section>
			<label for="title"><h5>Module Title</h5></label>
			<p class="input_prompt">A short title, ideally no more than 4 words</p> 
			<input id= "title" type="text" name="title" placeholder="">
		</section>

		<section> 
  			<label for="collab"><h5> Collaborators </h5></label>
  			<p class="input_prompt">Enter the names, exactly as you would like them to appear,
			of your collaborators in developing this module. Press enter after 
			each name.</p>
			<div id="collab-cont" class="tag-cont"></div>
		</section>
		  
		<section>
			<label for="content"><h5>Module Summary</h5></label>
			<p class="input_prompt">Please enter the module summary. This should a few sentences that 
			describes the content that will be covered in the module. 
			(100 Words)”</p>
		    <textarea class="mm_text_container" id= "content" name="content" placeholder="This module is an introduction into the genome and the terminology used to describe different types of genetic variation. We show how the genome is encoded as a string of nucleotides along one of the strands. We introduce different types of common genetic variation such as SNPs and indels and discuss how they are composed as alleles. We clarify how the terms mutation and variant are used in relation to (and sometimes interchangeably with) SNPs and indels. Finally, we discuss how genetic variation is arranged on haplotypes and within chromosomes."></textarea>
		</section>
		
		<section>
			<label for="sup-notes"><h5>Supplemental Notes and Resources</h5></label>
			<p class="input_prompt">This section will appear at the bottom of the module and guide the viewer 
				to other resources they may find helpful in understanding the content presented 
				in the module. This should not include links to papers, those should be provided as 
				DOIs later in the submission process.
			</p> 
		    <textarea class="mm_text_container" id= "sup-notes" name= "sup-notes" placeholder="For a hands on experience that will introduce you to the software programs using for genome sequencing, you may find the classes offered by the UCLA Collaboratory to be useful (https://qcb.ucla.edu/collaboratory/). Especially the class on Next-generation sequencing analysis."></textarea>
		</section>
		
		<section>
			<label for="acknowledge"><h5>Acknowledgements</h5></label>
			<p class="input_prompt">
			Please write an acknowledgement statement here where you acknowledge 
			non-collaborators who have helped to make this module possible. You 
			may also wish to acknowledge any funding sources. 
			</p>
		    <textarea class="mm_text_container" id="acknowledge" name="acknowledge" placeholder="I would like to acknowledge my advisor, Sriram Sankararaman, for allowing me the time and resources to construct this module and for training me in the topics covered within. I would also like to acknowledge my funding support from the NIH (66-666666) and UCLA Computational Medicine. The content of this module is solely the responsibility of its creators and does not necessarily represent the views of the funding sources. The funding sources played no role in the design or execution of the module."></textarea>
		</section>

		<section>
			<label for="ppt"><h5>Upload Presentation (PDF or PPT)</h5></label>
			<div id ="files-cont"> <span> </span> </div> 
			<input class="input_file" id="ppt" type="file" name="ppt" multiple onChange="add_file(event,this)">
			<label for="ppt">choose file </label> 
		</section>
		
		<section>
			<label for="pdf"><h5>Upload Worksheet (PDF)</h5></label>
			<div id="files-cont"> <span> </span></div> 
			<input class="input_file" id= "pdf" type="file" name="pdf" multiple onChange="add_file(event,this)">
			<label for="pdf">choose file </label> 
		</section>
		
		<section>
			<label for="tags"><h5>Key Terms</h5></label>
			<p class="input_prompt"> Please select up to five key terms from the following list. </p>
			<div id="key-cont" class="tag-cont"></div>
		</section>
		
		<section> 
			<label for="doi"><h5>DOIs</h5></label>
			<p class="input_prompt">Enter DOI numbers of papers or articles that are relevant to your module. </p>
			<div id="doi-cont" class="tag-cont"></div>
		</section> 

	</div>
	<?php wp_nonce_field( 'ajax-module-nonce', 'security' ); ?>
	<button id="submit" style="margin-left: 15px;"  class="submit_button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' }); ajax_module_js()" >Submit</button>
</div>

<?php get_footer() ?>