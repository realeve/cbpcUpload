/**
 * MES系统FTP服务器文件服务(用于文档、冠字等信息存储)
 * ftp://mes.cdyc.cbpm:2001/
 * http://mes.cdyc.cbpm:8000/
 */

/**
 * 在【冠字审核】流程中需要对上传至FTP的文件加载并以 ftp://user:psw@mes.cdyc.cbpm:2001/文件名.pdf 的形式访问。
 * 由于 Chrome 浏览器安全性的限制，在chrome 59及以上的版本中将不支持对FTP文件的加密访问，详情可参考以下链接：
 * https://www.chromestatus.com/feature/5669008342777856
 *
 *
 * 为此需要将原ftp服务转至http服务，本链接将对FTP上传、查看相关的接口做说明。
 */

// ---------------------------- lib.ts --------------------------------------------
export const FTP_UPLOAD_URL = "http://10.8.1.25/ftp";

export let dataURItoBlob = (dataURI: string) => {
  let byteString = atob(dataURI.split(",")[1]);
  let mimeString = dataURI.split(",")[0].split(":")[1].split(";")[0];
  let ab = new ArrayBuffer(byteString.length);
  let ia = new Uint8Array(ab);
  for (let i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }
  return new Blob([ab], {
    type: mimeString,
  });
};

export let blob2FormData = (blob: Blob, filename: string) => {
  let data = new FormData();
  let fileOfBlob = new File([blob], filename);
  data.append("file", fileOfBlob);
  return data;
};

export interface IFtpState {
  msg: string; // 正确或错误提醒
  status: 0 | 1; // 0表示出错，1为正确
  url?: string; // 上传文件地址
  name?: string; // 文件名。如果前台未绑定文件名，后台将自动生成一个随机文件
}

/**
 * 上传文件到 FTP 服务器
 * @param {string} dataURI base64 格式文件
 * @param {string} filename 文件名，不指定时系统自动生成
 */
export const uploadFile: (
  dataURI: string,
  filename: string
) => Promise<IFtpState> = (dataURI: string, filename?: string) => {
  let blob: Blob = dataURItoBlob(dataURI);
  let data: FormData = blob2FormData(blob, filename);
  return axios.post(FTP_UPLOAD_URL, data).then(({ data }) => data);
};

/**
 * 移除上传的文件
 * @param name 文件名，传入上传后返回的 name
 */
export const removeFile: (name: string) => Promise<IFtpState> = (
  name: string
) =>
  axios({
    url: FTP_UPLOAD_URL,
    params: { name },
  });
// ------------------------------- index.ts -----------------------------------------

import axios from "axios";
import { uploadFile, removeFile, IFtpData } from "./lib";

// 定义默认文件，内容为hello world
const txtDemoFile: string = "data:text/plain;base64,aGVsbG8gd29ybGQ=";
const filename: string = "hello.txt";

/**
 * tips:由于有移除文件这一接口存在，为了文件的安全性，上传后的文件系统会自动追加一串noncer防止恶意攻击。
 */
// 上传文件
uploadFile(txtDemoFile, filename).then<IFtpData>((res: IFtpData) => {
  console.log(res);
  /**
   * 返回内容如下：
    {
      msg: "上传成功",
      url: "nepal/hello_MDWAkwKKIF.txt",
      status: 1,
      name: "hello_MDWAkwKKIF.txt",
    }
   */
});

// 上例中上传的文件可以通过以下链接访问:
// http://mes.cdyc.cbpm:8000/nepal/hellos_MDWAkwKKIF.txt
// ftp://mes.cdyc.cbpm:2001/nepal/hellos_MDWAkwKKIF.txt

// 移除文件
removeFile("hellos_MDWAkwKKIF.txt");
