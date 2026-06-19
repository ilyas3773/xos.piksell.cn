(function () {
  var form = document.getElementById("installForm");
  var testBtn = document.getElementById("testButton");
  var installBtn = document.getElementById("installButton");
  var testResult = document.getElementById("testResult");
  var progressPanel = document.getElementById("progressPanel");
  var progressList = document.getElementById("progressList");
  var progressError = document.getElementById("progressError");
  var progressSuccess = document.getElementById("progressSuccess");
  if (!form) return;

  // 仅支持 MySQL。统一 db_type=mysql。
  function getFormData() {
    var data = new FormData(form);
    var obj = {};
    data.forEach(function (val, key) { obj[key] = val; });
    obj["db_type"] = "mysql";
    return obj;
  }

  function addProgressItem(time, message, isError) {
    var div = document.createElement("div");
    div.className = "progress-item";
    if (isError) div.style.color = "var(--danger)";
    div.innerHTML = "<span class=\"time\">" + time + "</span> " + message;
    progressList.appendChild(div);
    progressList.scrollTop = progressList.scrollHeight;
  }

  if (testBtn) {
    testBtn.addEventListener("click", function () {
      var data = getFormData();
      data["__action"] = "test";
      var dbType = data["db_type"] || "mysql";
      var missing = [];
      if (dbType === "sqlite") {
        if (!data["db_name"] || !String(data["db_name"]).trim()) missing.push("db_name(数据库文件)");
      } else {
        ["db_host", "db_user", "db_name"].forEach(function (k) {
          if (!data[k] || !String(data[k]).trim()) missing.push(k);
        });
      }
      if (missing.length) {
        testResult.innerHTML = '<div class="error-box" style="margin-top:10px;">请填写：' + missing.join("、") + '</div>';
        return;
      }
      testResult.innerHTML = '<div class="error-box" style="margin-top:10px;">测试中，请稍候...</div>';
      testBtn.disabled = true;
      fetch(window.location.pathname, {
        method: "POST",
        body: new URLSearchParams(data),
        headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" }
      }).then(function (r) { return r.json(); })
        .then(function (res) {
          testBtn.disabled = false;
          if (res.ok) {
            var msg = res.message;
            var subMsg = "";
            if (res.database_exists && res.database_empty) {
              subMsg = ' <span style="color:var(--success)">&#10003; 可以安装（库为空）</span>';
            } else if (res.database_exists && !res.database_empty) {
              subMsg = ' <span style="color:var(--danger)">&#10007; 库已有 ' + (res.table_count || 0) + ' 张表，请换空库</span>';
            } else {
              subMsg = ' <span style="color:var(--success)">&#10003; 库不存在，安装时会自动创建</span>';
            }
            testResult.innerHTML = '<div class="success-box" style="margin-top:10px;">' + msg + subMsg + '</div>';
          } else {
            testResult.innerHTML = '<div class="error-box" style="margin-top:10px;">' + (res.message || "测试失败") + '</div>';
          }
        })
        .catch(function (err) {
          testBtn.disabled = false;
          testResult.innerHTML = '<div class="error-box" style="margin-top:10px;">请求失败：' + err + '</div>';
        });
    });
  }

  if (installBtn) {
    installBtn.addEventListener("click", function () {
      if (!confirm("确认开始安装吗？")) return;
      var data = getFormData();
      var dbType = data["db_type"] || "mysql";
      var missing = [];
      if (dbType === "sqlite") {
        if (!data["db_name"] || !String(data["db_name"]).trim()) missing.push("db_name(数据库文件)");
      } else {
        ["db_host", "db_user", "db_name"].forEach(function (k) {
          if (!data[k] || !String(data[k]).trim()) missing.push(k);
        });
        ["admin_username", "admin_password", "admin_password_confirm"].forEach(function (k) {
          if (!data[k] || !String(data[k]).trim()) missing.push(k);
        });
      }
      if (missing.length) {
        alert("请填写：" + missing.join("、"));
        return;
      }
      installBtn.disabled = true;
      testResult.innerHTML = "";
      progressPanel.style.display = "block";
      progressList.innerHTML = "";
      progressError.style.display = "none";
      progressSuccess.style.display = "none";
      var data = getFormData();
      data["__action"] = "install";
      fetch(window.location.pathname, {
        method: "POST",
        body: new URLSearchParams(data),
        headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" }
      }).then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.steps) {
            res.steps.forEach(function (s) {
              addProgressItem(s.time, s.message);
            });
          }
          if (res.ok) {
            addProgressItem("---", "安装完成！", false);
            progressSuccess.style.display = "block";
            // 立即跳转到登录页面
            window.location.href = "/login.html";
            return;
          } else {
            addProgressItem("---", "安装失败", true);
            progressError.style.display = "block";
            progressError.textContent = "错误：" + (res.error || "未知错误");
            installBtn.disabled = false;
          }
        })
        .catch(function (err) {
          addProgressItem("---", "请求失败：" + err, true);
          installBtn.disabled = false;
        });
    });
  }
})();
