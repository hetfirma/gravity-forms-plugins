(function(){
  // Minimal helper
  function byId(id){ return document.getElementById(id); }

  // Debounce helper to limit API calls
  function debounce(fn, wait){
    let t;
    return function(){
      const ctx = this, args = arguments;
      clearTimeout(t);
      t = setTimeout(function(){ fn.apply(ctx, args); }, wait);
    };
  }

  // Expose a single global init function
  window.wbvInitNlAddress = function(formId, fieldId, options){
    options = options || {};
    var minChars = typeof options.minChars === 'number' ? options.minChars : 3;
    var defaultCountry = options.country || '';

    var base = "input_" + formId + "_" + fieldId;
    var street = byId(base + "_1"); // straat + nr
    var city   = byId(base + "_3"); // woonplaats
    var province = byId(base + "_4"); // Staat / provincie (subinput 4)
    var zip    = byId(base + "_5"); // postcode
    var country= byId(base + "_6"); // land

    if(!street){ return; }

    if (country && !country.value && defaultCountry) {
      country.value = defaultCountry;
    }

    // Create datalist once
    var listId = base + "_street_suggest";
    var dl = byId(listId);
    if(!dl){
      dl = document.createElement('datalist');
      dl.id = listId;
      document.body.appendChild(dl);
    }
    street.setAttribute('list', listId);

    // Suggest endpoint (no API key)
    async function fetchSuggest(q){
      var url = "https://api.pdok.nl/bzk/locatieserver/search/v3_1/suggest?q=" + encodeURIComponent(q) + "&fq=type:adres&rows=8";
      var res = await fetch(url, { credentials: "omit" });
      if(!res.ok){ return null; }
      return res.json();
    }

    // Free search for details after selection
    async function fetchDetails(q){
      var url = "https://api.pdok.nl/bzk/locatieserver/search/v3_1/free?q=" + encodeURIComponent(q) + "&fq=type:adres&rows=1";
      var res = await fetch(url, { credentials: "omit" });
      if(!res.ok){ return null; }
      return res.json();
    }

    var handleInput = debounce(async function(){
      var val = street.value.trim();
      if (val.length < minChars) { return; }

      try{
        var data = await fetchSuggest(val);
        dl.innerHTML = "";
        var docs = (data && data.response && data.response.docs) ? data.response.docs : [];
        docs.forEach(function(d){
          var opt = document.createElement('option');
          // d.weergavenaam e.g. "Straat 12, Plaats"
          opt.value = d.weergavenaam || "";
          opt.label = d.weergavenaam || "";
          dl.appendChild(opt);
        });
      }catch(e){
        // Silent
      }
    }, 200);

    street.addEventListener('input', handleInput);

    street.addEventListener('change', async function(){
      var val = street.value.trim();
      if (!val) return;
      try{
        var data = await fetchDetails(val);
        var doc = (data && data.response && data.response.docs && data.response.docs[0]) ? data.response.docs[0] : null;
        if(!doc) return;

        var straat = doc.straatnaam || "";
        var huisnr = (doc.huisnummer != null ? String(doc.huisnummer) : "");
        var huisltr= doc.huisletter || "";
        var nr = (huisnr + (huisltr || ""));
        street.value = [straat, nr].filter(Boolean).join(" ");

        if (city) {
          city.value = doc.woonplaatsnaam || doc.gemeentenaam || city.value;
        }

        // >>> provincie invullen (subinput _4)
        if (typeof province !== "undefined" && province) {
          province.value = doc.provincienaam || province.value;
        }

        if (zip && doc.postcode) {
          var pc = String(doc.postcode).toUpperCase().replace(/\s+/g,'');
          zip.value = pc.length === 6 ? (pc.slice(0,4) + " " + pc.slice(4)) : pc;
        }
      }catch(e){
        // Silent
      }
    });

  };
})();