<h2><?=$editorTitle?></h2>
<form method="post">
  <div class="box">
    <div class="column">
      <div class="row">
        <label for="tracker">Tracker</label>
        <select id="tracker" name="tracker">
          <option value="1" selected="selected">Bug</option>
          <option value="2">Feature</option>
          <option value="3">Support</option>
          <option value="4">Task</option>
        </select>
      </div>
    </div>
    <div class="row">
      <label for="title">Title</label>
      <input id="title" type="text" class="text" />
    </div>
    <div class="row">
      <label for="details">Details</label>
      <textarea id="details" type="details"></textarea>
    </div>
    <div class="column">
      <div class="row">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="1" selected="selected">New</option>
          <option value="12">Reviewed</option>
          <option value="13">Accepted</option>
          <option value="8">PatchesWelcome</option>
          <option value="10">GotPatch</option>
          <option value="2">InProgress</option>
          <option value="5">Fixed</option>
          <option value="16">WontFix</option>
          <option value="17">Invalid</option>
          <option value="27">Duplicate</option>
          <option value="29">CannotReproduce</option>
        </select>
      </div>
      <div class="row">
        <label for="priority">Priority</label>
        <select id="priority" name="priority">
          <option value="3">Low</option>
          <option value="4" selected="selected">Normal</option>
          <option value="5">High</option>
          <option value="6">Urgent</option>
          <option value="7">Immediate</option>
        </select>
      </div>
      <div class="row">
        <label for="version">Version</label>
        <select id="version" name="version">
          <option value=""></option>
          <option value="39">1.4.9</option>
        </select>
      </div>
    </div>
    <div class="column">
      <div class="row">
        <label for="platform">Platform</label>
        <select id="platform" name="platform">
          <option value=""></option>
          <option value="Windows">Windows</option>
          <option value="Mac OS X">Mac OS X</option>
          <option value="Linux">Linux</option>
          <option value="Unix">Unix</option>
          <option value="Various">Various</option>
        </select>
      </div>
      <div class="row">
        <label for="assignee">Assignee</label>
        <select id="assignee" name="assignee">
          <option value=""></option>
          <option value="40">Brendon Justin</option>
          <option value="4">Chris Schoeneman</option>
          <option value="49">Ed Carrel</option>
          <option value="10">Jason Axelson</option>
          <option value="482">Jean-Sébastien Dominique</option>
          <option value="2158">Jodi Jones</option>
          <option value="3">Nick Bolton</option>
          <option value="5">Sorin Sbârnea</option>
          <option value="57">Syed Amer Gilani</option>
        </select>
      </div>
    </div>
  </div>
  <div class="buttons">
    <input type="submit" value="Create" >
  </div>
</form>
